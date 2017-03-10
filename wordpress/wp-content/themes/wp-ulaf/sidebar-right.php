<aside class="sidebar" role="complementary">

  <?php if ( is_active_sidebar('widgetarea2') ) : ?>
    <?php dynamic_sidebar( 'widgetarea2' ); ?>
  <?php else : ?>

    <!-- If you want display static widget content - write code here
    RU: Здесь код вывода того, что необходимо для статического контента виджетов -->

  <?php endif; ?>
</aside><!-- /sidebar -->
