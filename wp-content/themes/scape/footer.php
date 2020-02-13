	                        </div><!-- #page-wrap  -->

                        <?php if ( wtbx_option_levelled('footer-enable') === '1' ) {
                            if ( wtbx_option_levelled('footer-block') !== '' && 'content_block' !== get_post_type() ) {
                                include(locate_template( 'templates/section-footer.php' ));
                            }
                        } ?>

                    </div><!-- #main  -->

                </div><!-- #wrapper -->

                <?php if (wtbx_search_panel()) include(locate_template('templates/header/header-search.php')); ?>
                <?php if (wtbx_overlay_panel()) include(locate_template('templates/header/header-overlay.php')); ?>
                <?php if (wtbx_sidearea_panel()) include(locate_template('templates/header/header-sidearea.php')); ?>

                <?php if ( wtbx_sticky_navbar() || wtbx_option('totop-enable', true) === '1' || wtbx_social_button() ) : ?>

                <div class="wtbx_fixed_navigation invisible clearfix">

                    <?php
                        if ( wtbx_option('totop-enable', true) === '1' ) : ?>
                            <a href="#wrapper" class="wtbx-totop <?php echo esc_attr( wtbx_option('totop-style', 'circle-shadow') ) ?>">
                                <div class="wtbx-totop-arrow front"><span></span></div>
                                <div class="wtbx-totop-arrow back"><span></span></div>
                            </a>
                        <?php endif;
                    ?>

                    <?php if ( wtbx_social_button() ) : ?>
                    <div class="wtbx-social-wrapper">
                        <div class="wtbx-social-trigger">
                            <i class="scape-ui-share"></i>
                        </div>
                        <div class="wtbx-social-container">
                            <div class="wtbx-social-inner">
                                <span class="wtbx-social-close"><i class="scape-ui-x"></i></span>
                                <?php include(locate_template('templates/components/share.php')); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ( wtbx_sticky_navbar() ) : ?>
                        <div class="wtbx_bottom_navigation"></div>
                    <?php endif; ?>

                </div>

            <?php endif; ?>

        </div><!-- #site -->

	<?php wp_footer(); ?>

    </body>
</html>