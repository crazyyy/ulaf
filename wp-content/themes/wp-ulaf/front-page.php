<?php /* Template Name: Home Page */ get_header(); ?>
    
    <?php if ( have_rows( 'slides' ) ) : ?>
        <!-- Hero Section with Image Slider -->
        <section class="hero" id="homehero">
            <div class="slider">
            <?php $num = isset($num) ? $num : 0; ?>
            <?php while ( have_rows( 'slides' ) ) : the_row(); ?>
                <?php $background = get_sub_field( 'background' ); ?>
                <?php if ( $background ) : ?>
                    <div class="slide <?php echo $num === 0 ? 'active' : ''; $num++;?>" style="background-image: url('<?php echo esc_url( $background['url'] ); ?>');">
                        <div class="slide-overlay"></div>
                        <div class="slide-content">
                            <h3 class="slide-title"><?php the_sub_field( 'title' ); ?></h1>
                            <p class="slide-subtitle"><?php the_sub_field( 'subtitle' ); ?></p>
                            <a href="<?php the_sub_field( 'hyperlink' ); ?>" class="cta-button"><?php the_sub_field( 'button' ); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
            </div>
        </section>
    <?php endif; ?>        

    <!-- Main Content -->
    <main class="main-content" itemscope itemprop="mainContentOfPage">
    
        <?php the_content(); ?>

        <?php
            // WP_Query to get the 3 latest news
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => 3,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
            );

            $news_query = new WP_Query($args);
        ?>

        <section class="section" id="news">
            <h2 class="section-title">Latest News</h2>
            <div class="news-grid">
                <?php if ($news_query->have_posts()) : ?>
                    <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                        <article class="news-card">
                            <div class="news-image" style="background-image: url('<?php echo get_the_post_thumbnail_url() ?: 'assets/default-news.png'; ?>');"></div>
                            <div class="news-content">
                                <div class="news-date"><?php echo get_the_date('F j, Y'); ?></div>
                                <h3 class="news-title"><?php the_title(); ?></h3>
                                <p class="news-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <p><?php _e('No news found.', 'text-domain'); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <?php
        // WP_Query для отримання всіх команд із custom post type "club"
        $args = array(
            'post_type' => 'club',
            'posts_per_page' => -1, // Отримати всі записи
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $teams_query = new WP_Query($args);
        ?>

        <section class="section" id="teams">
            <h2 class="section-title">Our Clubs</h2>
            <div class="teams-container">
                <div class="teams-slider">
                    <div class="teams-track" id="teamsTrack">
                        <?php if ($teams_query->have_posts()) : ?>
                            <?php while ($teams_query->have_posts()) : $teams_query->the_post(); ?>
                                <div class="team-card">
                                    <?php
                                    // Отримуємо логотип із featured image
                                    $thumbnail = get_the_post_thumbnail_url() ?: 'assets/default-team-logo.png';
                                    // Отримуємо терміни таксономії "cities"
                                    $cities = get_the_terms(get_the_ID(), 'cities');
                                    $city_name = $cities && !is_wp_error($cities) && !empty($cities) ? esc_html($cities[0]->name) : __('Unknown City', 'text-domain');
                                    // Отримуємо ACF поля для статистики (якщо вони є)
                                    $wins = get_field('wins') ?: 0;
                                    $losses = get_field('losses') ?: 0;
                                    $ties = get_field('ties') ?: 0;
                                    ?>
                                    <div class="team-logo">
                                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?> Logo">
                                    </div>
                                    <h3 class="team-name"><?php the_title(); ?></h3>
                                    <p class="team-location"><?php echo $city_name; ?>, Ukraine</p>
                                    <div class="team-record">
                                        <div class="team-record-item">
                                            <div class="team-record-value"><?php echo esc_html($wins); ?></div>
                                            <div class="team-record-label">Wins</div>
                                        </div>
                                        <div class="team-record-item">
                                            <div class="team-record-value"><?php echo esc_html($losses); ?></div>
                                            <div class="team-record-label">Losses</div>
                                        </div>
                                        <div class="team-record-item">
                                            <div class="team-record-value"><?php echo esc_html($ties); ?></div>
                                            <div class="team-record-label">Ties</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p><?php _e('No teams found.', 'text-domain'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="slider-nav prev" id="teamsPrev">‹</button>
                <button class="slider-nav next" id="teamsNext">›</button>
                <div class="slider-indicators" id="teamsIndicators"></div>
            </div>
        </section>
<?php /* ?>
        <!-- Statistics Section -->
        <section class="section" id="statistics">
            <h2 class="section-title">Player Statistics</h2>
            <div class="stats-container">
                <div class="stats-controls">
                    <button class="filter-btn active" data-filter="all">All Players</button>
                    <button class="filter-btn" data-filter="offense">Offense</button>
                    <button class="filter-btn" data-filter="defense">Defense</button>
                    <button class="filter-btn" data-filter="special">Special Teams</button>
                </div>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Team</th>
                            <th>Position</th>
                            <th>Touchdowns</th>
                            <th>Yards</th>
                            <th>Tackles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-position="offense">
                            <td>Oleksandr Kovalenko</td>
                            <td>Kyiv Thunder</td>
                            <td>QB</td>
                            <td>18</td>
                            <td>2,847</td>
                            <td>-</td>
                        </tr>
                        <tr data-position="offense">
                            <td>Dmytro Petrenko</td>
                            <td>Lviv Lions</td>
                            <td>RB</td>
                            <td>12</td>
                            <td>1,456</td>
                            <td>-</td>
                        </tr>
                        <tr data-position="defense">
                            <td>Viktor Shevchenko</td>
                            <td>Odesa Sharks</td>
                            <td>LB</td>
                            <td>-</td>
                            <td>-</td>
                            <td>89</td>
                        </tr>
                        <tr data-position="offense">
                            <td>Andriy Bondarenko</td>
                            <td>Kharkiv Eagles</td>
                            <td>WR</td>
                            <td>9</td>
                            <td>1,234</td>
                            <td>-</td>
                        </tr>
                        <tr data-position="defense">
                            <td>Maxim Ivanov</td>
                            <td>Dnipro Dynamo</td>
                            <td>DB</td>
                            <td>-</td>
                            <td>-</td>
                            <td>67</td>
                        </tr>
                        <tr data-position="special">
                            <td>Roman Tkachenko</td>
                            <td>Kyiv Thunder</td>
                            <td>K</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
<?php */ ?>
<?php /* ?>
        <!-- Gallery Section -->
        <section class="section" id="gallery">
            <h2 class="section-title">Gallery</h2>
            <div class="gallery-container">
                <div class="gallery-slider">
                    <div class="gallery-track" id="galleryTrack">
                        <div class="gallery-slide" style="background-image: url('assets/gallery-1.png?prompt=American%20football%20game%20action%20shot%2C%20players%20tackling%2C%20blue%20uniforms%2C%20stadium%20crowd%2C%20professional%20sports%20photography');">
                            <div class="gallery-overlay">
                                <h3 class="gallery-title">Championship Game Action</h3>
                                <p class="gallery-description">Intense moments from the ULAF Championship finals</p>
                            </div>
                        </div>
                        <div class="gallery-slide" style="background-image: url('assets/gallery-2.png?prompt=American%20football%20training%20session%2C%20players%20practicing%20drills%2C%20coaching%20staff%2C%20outdoor%20field');">
                            <div class="gallery-overlay">
                                <h3 class="gallery-title">Training Excellence</h3>
                                <p class="gallery-description">Teams preparing for the upcoming season</p>
                            </div>
                        </div>
                        <div class="gallery-slide" style="background-image: url('assets/gallery-3.png?prompt=American%20football%20victory%20celebration%2C%20team%20lifting%20trophy%2C%20confetti%2C%20stadium%20lights%2C%20championship%20moment');">
                            <div class="gallery-overlay">
                                <h3 class="gallery-title">Victory Celebration</h3>
                                <p class="gallery-description">Champions celebrating their hard-earned victory</p>
                            </div>
                        </div>
                        <div class="gallery-slide" style="background-image: url('assets/gallery-4.png?prompt=American%20football%20youth%20program%2C%20young%20players%20learning%2C%20coach%20instruction%2C%20development%20training');">
                            <div class="gallery-overlay">
                                <h3 class="gallery-title">Youth Development</h3>
                                <p class="gallery-description">Building the next generation of Ukrainian football players</p>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="slider-nav prev" id="galleryPrev">‹</button>
                <button class="slider-nav next" id="galleryNext">›</button>
                <div class="slider-indicators" id="galleryIndicators"></div>
            </div>
        </section>
<?php */ ?>
<?php /* ?>
        <!-- Next Game Section -->
        <section class="section" id="next-game">
            <h2 class="section-title">Next Game</h2>
            <div class="next-game-container">
                <div class="next-game-bg"></div>
                <div class="next-game-content">
                    <?php
                    // WP_Query для отримання найближчої події
                    $args = array(
                        'post_type' => 'event',
                        'posts_per_page' => 1,
                        'post_status' => 'publish',
                        'meta_key' => 'date',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                        'meta_query' => array(
                            array(
                                'key' => 'date',
                                'value' => date('Ymd'),
                                'compare' => '>=',
                                'type' => 'DATE'
                            )
                        )
                    );
                    $event_query = new WP_Query($args);

                    if ($event_query->have_posts()) :
                        while ($event_query->have_posts()) : $event_query->the_post();
                    ?>
                        <h3 class="next-game-title"><?php echo esc_html(get_field('title') ?: 'Upcoming Match'); ?></h3>
                        <div class="game-teams">
                            <?php $home_team = get_field('home_team'); ?>
                            <?php if ($home_team && is_array($home_team)) : ?>
                                <div class="game-team">
                                    <?php foreach ($home_team as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <div class="game-team-logo">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <img src="<?php echo esc_url(get_the_post_thumbnail_url($post, 'thumbnail')); ?>" alt="<?php the_title_attribute(); ?> Logo">
                                            <?php else : ?>
                                                <div>No Logo</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="game-team-name"><?php the_title(); ?></div>
                                    <?php endforeach; ?>
                                    <?php wp_reset_postdata(); ?>
                                </div>
                            <?php endif; ?>
                            <div class="game-vs">VS</div>
                            <?php $guest_team = get_field('guest_team'); ?>
                            <?php if ($guest_team && is_array($guest_team)) : ?>
                                <div class="game-team">
                                    <?php foreach ($guest_team as $post) : ?>
                                        <?php setup_postdata($post); ?>
                                        <div class="game-team-logo">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <img src="<?php echo esc_url(get_the_post_thumbnail_url($post, 'thumbnail')); ?>" alt="<?php the_title_attribute(); ?> Logo">
                                            <?php else : ?>
                                                <div>No Logo</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="game-team-name"><?php the_title(); ?></div>
                                    <?php endforeach; ?>
                                    <?php wp_reset_postdata(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="game-info">
                            <div class="game-detail">
                                <div class="game-detail-label">Date</div>
                                <div class="game-detail-value"><?php echo esc_html(get_field('date') ?: 'TBD'); ?></div>
                            </div>
                            <div class="game-detail">
                                <div class="game-detail-label">Time</div>
                                <div class="game-detail-value"><?php echo esc_html(get_field('time') ?: 'TBD'); ?></div>
                            </div>
                            <div class="game-detail">
                                <div class="game-detail-label">Venue</div>
                                <div class="game-detail-value"><?php echo esc_html(get_field('venue') ?: 'TBD'); ?></div>
                            </div>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="cta-button">Read More</a>
                    <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                    ?>
                        <p class="no-events">No upcoming events found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
<?php */ ?>
<?php /* ?>
        <!-- Newsletter Section -->
        <section class="section" id="newsletter">
            <h2 class="section-title">Stay Updated</h2>
            <div class="newsletter-container">
                <p class="about-text">Subscribe to our newsletter and never miss the latest ULAF news, game schedules, and exclusive content.</p>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>
            </div>
        </section>
<?php */ ?>
        <?php
        // WP_Query для отримання всіх партнерів із custom post type "partner"
        $args = array(
            'post_type' => 'partner',
            'posts_per_page' => -1, // Отримати всі записи
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $partners_query = new WP_Query($args);
        ?>

        <section class="section" id="partners">
            <h2 class="section-title">Our Partners</h2>
            <div class="partners-container">
                <div class="partners-grid">
                    <?php if ($partners_query->have_posts()) : ?>
                        <?php while ($partners_query->have_posts()) : $partners_query->the_post(); ?>
                            <?php
                            // Get the thumbnail URL, if any
                            $thumbnail = get_the_post_thumbnail_url();
                            $style = $thumbnail ? 'style="background-image: url(\'' . esc_url($thumbnail) . '\');"' : '';
                            ?>
                            <div class="partner-logo" <?php echo $style; ?>>
                                <?php if (!$thumbnail) : ?>
                                    <?php the_title(); ?>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else : ?>
                        <p><?php _e('No partners found.', 'text-domain'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>

<?php get_footer(); ?>