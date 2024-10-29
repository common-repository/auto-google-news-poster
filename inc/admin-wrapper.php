<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h1>
        <?php esc_html_e($this->name, 'agnp-plugin'); ?>
    </h1>
    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <h2 class="hndle"><span>
                                <i class="genericond genericon genericon-cog"></i>
                                <?php esc_html_e('Settings', 'agnp-plugin'); ?>
                            </span>
                        </h2>
                        <div class="inside">
                            <form method="post"  action="admin.php" >
                                <input type="hidden" name="action" value="get_agnp" />
                                <?php wp_nonce_field('get_agnp_action'); ?>
                                <table class="form-table ccs-table">
                                    <tr valign="top">
                                        <td scope="row">
                                            <label for="tablecell">
                                                <?php esc_html_e('Search News', 'agnp-plugin'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input type="text" value="" name="news-search" class="regular-text" /><br>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <td scope="row">
                                            <label for="tablecell">
                                                <?php esc_html_e('Select Topic', 'agnp-plugin'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <select name="news-topic">
                                                <option value="no">No</option>
                                                <option value="sfy">Suggested for you</option>
                                                <option value="n">Nation</option>
                                                <option value="w">World</option>
                                                <option value="b">Business</option>
                                                <option value="tc">Technology</option>
                                                <option value="e">Entertainment</option>
                                                <option value="s">Sports</option>
                                                <option value="m">Health</option>
                                            </select>
                                        </td>
                                    </tr>
									</table>
                                <?php submit_button('Search'); ?>
                            </form>
                            <br class="clear" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->
                        <h2 class="hndle"><span>
                                <?php
                                esc_attr_e(
                                        'Sidebar Content Header', 'agnp-plugin'
                                );
                                ?></span>
                        </h2>
                        <div class="inside">
                            <p>
                                <?php
                                esc_attr_e(
                                        'Search news by enter keywords or select topic', 'agnp-plugin'
                                );
                                ?>
                            </p>
                        </div>
                        <!-- .inside -->
                    </div>
                    <!-- .postbox -->
                </div>
                <!-- .meta-box-sortables -->
            </div>
            <!-- #postbox-container-1 .postbox-container -->
        </div>
        <br class="clear">
    </div>
</div>