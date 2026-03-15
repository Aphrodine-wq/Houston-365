<?php
/**
 * Sport View: Staff
 *
 * Renders coaching/support staff for the selected sport.
 *
 * Expected args (optional):
 * - sport_term (WP_Term)
 * - sport_context (string) ACF term context (taxonomy_termId)
 * - staff_members (array) Pre-hydrated staff data
 */

$sport_term    = $args['sport_term'] ?? get_queried_object();
$sport_context = $args['sport_context'] ?? ($sport_term instanceof WP_Term ? sprintf('%s_%d', $sport_term->taxonomy, $sport_term->term_id) : null);

$staff_members = $args['staff_members'] ?? [];

if (empty($staff_members)) {
    /**
     * Filter: hts_sport_staff_members
     *
     * @param array   $staff Array of members with keys: name, title, email, phone, bio, headshot_id.
     * @param WP_Term $term  Current sport term.
     */
    $staff_members = apply_filters('hts_sport_staff_members', [], $sport_term);
}

if (empty($staff_members) && function_exists('get_field') && $sport_context) {
    $acf_staff = get_field('staff_members', $sport_context);

    if (is_array($acf_staff)) {
        $staff_members = array_map(static function ($member) {
            return [
                'name'       => $member['name'] ?? ($member['full_name'] ?? ''),
                'title'      => $member['title'] ?? ($member['role'] ?? ''),
                'email'      => $member['email'] ?? '',
                'phone'      => $member['phone'] ?? '',
                'bio'        => $member['bio'] ?? ($member['notes'] ?? ''),
                'headshot_id'=> $member['headshot'] ?? ($member['photo'] ?? null),
            ];
        }, $acf_staff);
    }
}

// Normalize entries and drop empties.
$staff_members = array_values(array_filter(array_map(static function ($member) {
    if (!is_array($member)) {
        return null;
    }

    $name  = trim($member['name'] ?? '');
    $title = trim($member['title'] ?? '');

    if ($name === '' && $title === '') {
        return null;
    }

    return [
        'name'        => $name,
        'title'       => $title,
        'email'       => trim($member['email'] ?? ''),
        'phone'       => trim($member['phone'] ?? ''),
        'bio'         => trim($member['bio'] ?? ''),
        'headshot_id' => $member['headshot_id'] ?? $member['headshot'] ?? null,
    ];
}, $staff_members)));
?>
<?php if (!empty($staff_members)) : ?>
    <div class="sport-staff">
        <?php foreach ($staff_members as $member) : ?>
            <article class="sport-staff__card">
                <div class="sport-staff__headshot" aria-hidden="true">
                    <?php
                    if (!empty($member['headshot_id'])) {
                        echo wp_get_attachment_image($member['headshot_id'], 'thumbnail', false, ['alt' => $member['name']]);
                    }
                    ?>
                </div>
                <div class="sport-staff__body">
                    <?php if ($member['name']) : ?>
                        <h2 class="sport-staff__name"><?php echo esc_html($member['name']); ?></h2>
                    <?php endif; ?>

                    <?php if ($member['title']) : ?>
                        <p class="sport-staff__title"><?php echo esc_html($member['title']); ?></p>
                    <?php endif; ?>

                    <?php if ($member['email'] || $member['phone']) : ?>
                        <p class="sport-staff__meta">
                            <?php if ($member['email']) : ?>
                                <a href="mailto:<?php echo esc_attr($member['email']); ?>"><?php echo esc_html($member['email']); ?></a>
                            <?php endif; ?>
                            <?php if ($member['email'] && $member['phone']) : ?>
                                <span> &middot; </span>
                            <?php endif; ?>
                            <?php if ($member['phone']) : ?>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $member['phone'])); ?>"><?php echo esc_html($member['phone']); ?></a>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($member['bio']) : ?>
                        <div class="sport-staff__bio">
                            <?php echo wpautop(esc_html($member['bio'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="panel">
        <p>No staff has been added for this sport yet. Populate the <strong>Staff Members</strong> repeater or hook into <code>hts_sport_staff_members</code>.</p>
    </div>
<?php endif; ?>
