<?php
/**
 * Sport View: Class Breakdown
 *
 * Displays roster distribution by class (freshman, sophomore, etc.)
 *
 * Expected args (optional):
 * - sport_term (WP_Term)
 * - sport_context (string)
 * - class_sections (array) Pre-injected data
 */

$sport_term    = $args['sport_term'] ?? get_queried_object();
$sport_context = $args['sport_context'] ?? ($sport_term instanceof WP_Term ? sprintf('%s_%d', $sport_term->taxonomy, $sport_term->term_id) : null);

$class_sections = $args['class_sections'] ?? [];

if (empty($class_sections)) {
    /**
     * Filter: hts_sport_class_breakdown
     *
     * @param array   $sections Normalized array of sections (see below).
     * @param WP_Term $term     Current sport term.
     */
    $class_sections = apply_filters('hts_sport_class_breakdown', [], $sport_term);
}

if (empty($class_sections) && function_exists('get_field') && $sport_context) {
    $acf_sections = get_field('class_breakdown', $sport_context);

    if (is_array($acf_sections)) {
        $class_sections = $acf_sections;
    }
}

$normalize = static function ($sections) {
    if (!is_array($sections)) {
        return [];
    }

    $normalized = [];

    foreach ($sections as $section) {
        if (!is_array($section)) {
            continue;
        }

        $title = $section['title'] ?? ($section['heading'] ?? '');
        $description = $section['description'] ?? ($section['summary'] ?? '');

        $rows = $section['rows'] ?? ($section['items'] ?? ($section['entries'] ?? []));
        if (!is_array($rows)) {
            $rows = [];
        }

        $normalized_rows = [];
        foreach ($rows as $row) {
            if (is_string($row)) {
                [$label, $value] = array_map('trim', array_pad(explode('|', $row, 2), 2, ''));
                if ($label === '' && $value === '') {
                    continue;
                }
                $normalized_rows[] = [
                    'label' => $label,
                    'value' => $value,
                    'note'  => '',
                ];
                continue;
            }

            if (!is_array($row)) {
                continue;
            }

            $label = trim($row['label'] ?? ($row['class'] ?? ''));
            $value = trim((string) ($row['value'] ?? ($row['count'] ?? '')));
            $note  = trim($row['note'] ?? ($row['details'] ?? ''));

            if ($label === '' && $value === '') {
                continue;
            }

            $normalized_rows[] = [
                'label' => $label,
                'value' => $value,
                'note'  => $note,
            ];
        }

        if (empty($normalized_rows)) {
            continue;
        }

        $normalized[] = [
            'title'       => (string) $title,
            'description' => (string) $description,
            'rows'        => $normalized_rows,
        ];
    }

    return $normalized;
};

$class_sections = $normalize($class_sections);
?>
<?php if (!empty($class_sections)) : ?>
    <div class="sport-class">
        <?php foreach ($class_sections as $section) : ?>
            <section class="sport-class__group">
                <?php if ($section['title']) : ?>
                    <h2 class="sport-class__title"><?php echo esc_html($section['title']); ?></h2>
                <?php endif; ?>
                <?php if ($section['description']) : ?>
                    <p class="sport-class__description"><?php echo esc_html($section['description']); ?></p>
                <?php endif; ?>

                <div class="sport-class__list">
                    <?php foreach ($section['rows'] as $row) : ?>
                        <div>
                            <div class="sport-class__item">
                                <span class="sport-class__label"><?php echo esc_html($row['label']); ?></span>
                                <span class="sport-class__value"><?php echo esc_html($row['value']); ?></span>
                            </div>
                            <?php if (!empty($row['note'])) : ?>
                                <p class="sport-class__note"><?php echo esc_html($row['note']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="panel">
        <p>No class breakdown data is available. Populate the <strong>Class Breakdown</strong> repeater or hook into <code>hts_sport_class_breakdown</code>.</p>
    </div>
<?php endif; ?>
