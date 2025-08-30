    <!-- Footer -->
    <footer class="footer" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-section">
                    <h3>Ukrainian League of American Football</h3>
                    <p>УЛАФ прагне просувати американський футбол в Україні, розвивати місцеві таланти та створювати захопливі можливості як для спортсменів, так і для вболівальників. Приєднуйтесь до нас у побудові майбутнього українського американського футболу.</p>
                </div>
                <div class="footer-section">
                    <h3>Соціальні мережі</h3>
                    <ul class="footer-nav">
                        <li><a href="https://www.facebook.com/ulaf.ua/">Facebook</a></li>
                        <li><a href="https://www.instagram.com/ua_ulaf">Instagram</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Контактна інформація</h3>
                    <p>Електронна пошта: <a href="mailto:ulaf.office@gmail.com">ulaf.office@gmail.com</a></p>
                    <p>Телефон: +380 44 123 4567</p>
                    <p>Адреса: Київ, Україна</p>
                </div>
                <?php
                    // WP_Query to get all sponsors with custom post type "sponsor"
                    $args = array(
                        'post_type' => 'sponsor',
                        'posts_per_page' => -1, // Get all records
                        'post_status' => 'publish',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    );

                    $sponsors_query = new WP_Query($args);
                ?>

                <div class="footer-section">
                    <h3>Sponsors</h3>
                    <div class="footer-partners">
                        <?php if ($sponsors_query->have_posts()) : ?>
                            <?php while ($sponsors_query->have_posts()) : $sponsors_query->the_post(); ?>
                                <div class="footer-partner"><?php the_title(); ?></div>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p><?php _e('No sponsors found.', 'text-domain'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Ukrainian League of American Football. All rights reserved.</p>
                <div class="footer-bottom-links">
                    <a href="/privacy">Privacy Policy</a>
                    <a href="/terms">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <img class="lightbox-image" id="lightboxImage" src="" alt="">
            <button class="lightbox-close" id="lightboxClose">×</button>
        </div>
    </div>

    <?php wp_footer(); ?>
</body>
</html>