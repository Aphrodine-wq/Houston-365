<?php
/**
 * Sport View: Standings
 *
 * Attempts to render conference/division standings for the current sport term.
 *
 * Data sources (checked in this order):
 *  1. `$args['standings_sections']` passed from the template.
 *  2. `hts_sport_standings_data` filter, which should return a normalized array.
 *  3. Advanced Custom Fields term meta (`standings_sections` or `standings_table`).
 *
 * Normalized structure:
 * [
 *   [
 *     'title'   => 'SEC West',
 *     'columns' => ['Team', 'Overall', 'Conference'],
 *     'rows'    => [
 *        ['label' => 'Houston', 'cells' => ['10-2', '6-2']],
 *        ...
 *     ],
 *   ],
 * ]
 */

$sport_term = $args['sport_term'] ?? get_queried_object();
$sport_context = $args['sport_context'] ?? ($sport_term instanceof WP_Term ? sprintf('%s_%d', $sport_term->taxonomy, $sport_term->term_id) : null);

/**
 * Normalize a set of sections into the structure described above.
 *
 * @param mixed $raw_sections
 * @return array
 */
$normalize_sections = static function ($raw_sections) {
    if (!is_array($raw_sections)) {
        return [];
    }

    $sections = [];

    foreach ($raw_sections as $section) {
        if (!is_array($section)) {
            continue;
        }

        // If the section already looks normalized, trust it.
        if (isset($section['columns'], $section['rows']) && is_array($section['columns']) && isset($section['rows'][0]['cells'])) {
            $sections[] = [
                'title'   => $section['title'] ?? ($section['heading'] ?? ''),
                'columns' => array_values(array_map('strval', $section['columns'])),
                'rows'    => array_map(static function ($row) {
                    return [
                        'label' => $row['label'] ?? ($row['team'] ?? ''),
                        'cells' => array_values(array_map('strval', $row['cells'] ?? [])),
                    ];
                }, $section['rows']),
            ];
            continue;
        }

        $title = $section['title'] ?? ($section['heading'] ?? ($section['label'] ?? ''));

        $columns = $section['columns'] ?? ($section['headers'] ?? []);
        if (is_string($columns)) {
            $columns = array_map('trim', explode(',', $columns));
        }
        if (!is_array($columns) || empty($columns)) {
            $columns = ['Team', 'Record'];
        }

        $raw_rows = $section['rows'] ?? ($section['table_rows'] ?? ($section['entries'] ?? []));
        $rows = [];

        foreach ($raw_rows as $row) {
            if (is_string($row)) {
                $cells = array_map('trim', explode('|', $row));
                $label = array_shift($cells);
                $rows[] = [
                    'label' => $label,
                    'cells' => $cells,
                ];
                continue;
            }

            if (!is_array($row)) {
                continue;
            }

            $label = $row['label'] ?? ($row['team'] ?? ($row['name'] ?? ''));

            $cells = $row['cells'] ?? [
                $row['overall'] ?? null,
                $row['conference'] ?? null,
                $row['record'] ?? null,
                $row['league'] ?? null,
            ];

            if (is_string($cells)) {
                $cells = array_map('trim', explode('|', $cells));
            }

            if (!is_array($cells)) {
                $cells = array_filter([$cells]);
            }

            $cells = array_values(array_filter(array_map(static function ($value) {
                return is_scalar($value) ? (string) $value : '';
            }, $cells), static function ($value) {
                return $value !== '';
            }));

            $rows[] = [
                'label' => (string) $label,
                'cells' => $cells,
            ];
        }

        $sections[] = [
            'title'   => (string) $title,
            'columns' => array_values(array_map('strval', $columns)),
            'rows'    => $rows,
        ];
    }

    return array_values(array_filter($sections, static function ($section) {
        return !empty($section['rows']);
    }));
};

$standings_sections = $args['standings_sections'] ?? [];

if (empty($standings_sections)) {
    /**
     * Filter: hts_sport_standings_data
     *
     * Gives developers full control over the standings data passed to the view.
     *
     * @param array   $sections Normalized array (see docblock format above).
     * @param WP_Term $term     Current sport term.
     */
    $standings_sections = apply_filters('hts_sport_standings_data', [], $sport_term);
}

if (empty($standings_sections) && function_exists('get_field') && $sport_context) {
    $acf_sections = get_field('standings_sections', $sport_context);

    if (empty($acf_sections)) {
        $acf_sections = get_field('standings_table', $sport_context);
    }

    $standings_sections = $acf_sections ?: [];
}

$standings_sections = $normalize_sections($standings_sections);
?>
<?php if (!empty($standings_sections)) : ?>
    <div class="sport-standings">
        <?php foreach ($standings_sections as $section) : ?>
            <section class="sport-standings__group" aria-label="<?php echo esc_attr($section['title'] ?: 'Standings'); ?>">
                <?php if (!empty($section['title'])) : ?>
                    <h2 class="sport-standings__title"><?php echo esc_html($section['title']); ?></h2>
                <?php endif; ?>
                <div class="sport-standings__table-wrapper">
                    <table>
                        <?php if (!empty($section['columns'])) : ?>
                            <thead>
                                <tr>
                                    <th scope="col"><?php echo esc_html($section['columns'][0]); ?></th>
                                    <?php foreach (array_slice($section['columns'], 1) as $column) : ?>
                                        <th scope="col"><?php echo esc_html($column); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                        <?php endif; ?>
                        <tbody>
                            <?php foreach ($section['rows'] as $row) : ?>
                                <tr>
                                    <th scope="row" class="sport-standings__team"><?php echo esc_html($row['label']); ?></th>
                                    <?php foreach ($row['cells'] as $cell) : ?>
                                        <td><?php echo esc_html($cell); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="panel">
        <p>Standings have not been added for this sport yet. Add them via the term meta fields or filter <code>hts_sport_standings_data</code>.</p>
    </div>
<?php endif; ?>
