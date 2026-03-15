<?php
$home_away = $args['home_away']   ?? '';
$opponent  = $args['opponent']    ?? '';
$hs        = $args['home_score']  ?? '';
$as        = $args['away_score']  ?? '';
$final     = $args['final_score'] ?? '';
$date_raw  = $args['date']        ?? '';
$time_raw  = $args['time']        ?? '';
$venue     = $args['venue']       ?? '';
$permalink = $args['permalink']   ?? '';

$date = $date_raw ? DateTime::createFromFormat('Ymd', $date_raw)->format('F j, Y') : '';
$time = $time_raw ? date('g:i a', strtotime($time_raw)) : '';
?>
<section class="hts-scoreboard<?php echo $permalink ? ' hts-scoreboard--link' : ''; ?>">
    <?php if ($permalink) : ?><a class="hts-scoreboard__link" href="<?php echo esc_url($permalink); ?>"><?php endif; ?>
        <div class="hts-scorelline">
            <?php
                if ($final) {
                    echo esc_html($final);
                } elseif ($hs !== '' && $as !== '') {
                    $homeLabel = ($home_away === 'home') ? 'Houston' : $opponent;
                    $awayLabel = ($home_away === 'away') ? 'Houston' : $opponent;
                    echo esc_html("$homeLabel $hs "); echo '<span class="vs">-</span> '; echo esc_html("$as $awayLabel");
                } else {
                    echo esc_html($opponent ?: 'TBD');
                }
            ?>
        </div>
        <div class="hts-meta">
            <?php echo esc_html(trim($date . ($time ? " @ $time" : ''))); ?>
            <?php if ($venue) echo ' · '.esc_html($venue); ?>
        </div>
    <?php if ($permalink) : ?></a><?php endif; ?>
</section>
