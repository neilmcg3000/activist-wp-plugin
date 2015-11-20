<div class="wrap">

	<h2><?php esc_html_e('Activist' , 'activist');?></h2>

  <div id="wpcom-stats-meta-box-container" class="metabox-holder"><?php
  				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
  				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
  				?>
    <div class="postbox-container" style="width: 55%;margin-right: 10px;">
    	<div id="normal-sortables" class="meta-box-sortables ui-sortable">
        <div id="referrers" class="postbox">
					<div class="handlediv" title="Click to toggle"><br></div>
					<h3 class="hndle"><span><?php esc_html_e( 'Settings' , 'activist');?></span></h3>
					<form name="activist_conf" id="activist-conf" action="<?php echo esc_url(Activist_Admin::get_page_url()); ?>" method="POST">
						<div class="inside">
							<table cellspacing="0" class="activist-settings">
								<tbody>
                  <tr>
                    <th align="left" scope="row"><?php esc_html_e('Cache Mode', 'activist');?></th>
          					<td></td>
                  	<td align="left">
                      <fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Activist Cache Mode', 'activist'); ?></span></legend>
                      <p><label for="activist_mode_1"><input type="radio" name="activist_cache_mode" id="activist_mode_1" value="1" <?php checked('1', $cache_mode); ?> /> <?php esc_html_e('Display errors when interference occurs.', 'activist'); ?></label></p>
                      <p><label for="activist_mode_2"><input type="radio" name="activist_cache_mode" id="activist_mode_2" value="2" <?php checked('2', $cache_mode); ?> /> <?php esc_html_e('Clone full blog for offline display.', 'activist'); ?></label></p>
											<!--<p><label for="activist_mode_3"><input type="radio" name="activist_cache_mode" id="activist_mode_3" value="3" <?php checked('3', $cache_mode); ?> /> <?php esc_html_e('Clone some pages for offline display.', 'activist'); ?></label></p>-->
                      </fieldset>
											<script type='text/javascript'>
												window.addEventListener('load', function() {
													var m1 = document.getElementById('activist_mode_1');
													var m2 = document.getElementById('activist_mode_2');

													var onChange = function() {
														var ol = document.getElementById('offline_label');
														var o1 = document.getElementById('activist_offline_warn');
														var o2 = document.getElementById('activist_offline_post');
														var os = document.getElementById('activist_offline_chooser');
														if (m1.checked) {
															ol.style.color = 'inherit';
															os.disabled = o1.disabled = o2.disabled = false;
														} else {
															ol.style.color = '#888';
															os.disabled = o1.disabled = o2.disabled = true;
														}
													};
													onChange();
													m1.addEventListener('change', onChange, true);
													m2.addEventListener('change', onChange, true);
												}, true);
											</script>
      							</td>
                  </tr>
									<tr>
										<th align="left" scope="row" id='offline_label'><?php esc_html_e('When Offline', 'activist');?></th>
										<td></td>
										<td align="left">
											<p>
												<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Activist Offline Behavior', 'activist'); ?></span></legend>
	                      <p><label for="activist_offline_warn"><input type="radio" name="activist_offline_behavior" id="activist_offline_warn" value="0" <?php checked('0', $offline_behavior); ?> /> <?php esc_html_e('Show a "You are disconnected" message.', 'activist'); ?></label></p>
	                      <p><label for="activist_offline_post"><input type="radio" name="activist_offline_behavior" id="activist_offline_post" value="<?php echo $offline_behavior; ?>" <?php if($offline_behavior > 0) {echo("checked='checked'");} ?> />
													<span style='display:inline-block'>
													<?php esc_html_e('Show page:', 'activist'); ?>
													<?php wp_dropdown_pages(array( 'name' => 'activist_offline_chooser', 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $offline_behavior)); ?>
													<script type='text/javascript'>
														var oselector = document.getElementById('activist_offline_chooser');
														oselector.addEventListener('change', function() {
															var specific = document.getElementById('activist_offline_post');
															specific.checked = 'checked';
															specific.value = oselector.value;
														}, true);
													</script>
												</span>
												</label>
												</p>
	                      </fieldset>
											</p>
										</td>
									</tr>
									<tr>
										<th align="left" scope="row"><?php esc_html_e('When Censored', 'activist');?></th>
										<td></td>
										<td align="left">
											<p>
												<fieldset><legend class="screen-reader-text"><span><?php esc_html_e('Activist Censored Behavior', 'activist'); ?></span></legend>
												<p><label for="activist_censor_warn"><input type="radio" name="activist_censor_behavior" id="activist_censor_warn" value="0" <?php checked('0', $censor_behavior); ?> /> <?php esc_html_e('Show default warning.', 'activist'); ?></label></p>
												<p><label for="activist_censor_post"><input type="radio" name="activist_censor_behavior" id="activist_censor_post" value="<?php echo $censor_behavior; ?>" <?php if($censor_behavior > 0) {echo("checked='checked'");} ?> />
													<span style='display:inline-block'>
													<?php esc_html_e('Show page:', 'activist'); ?>
													<?php wp_dropdown_pages(array( 'name' => 'activist_censor_chooser', 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => $censor_behavior)); ?>
													<script type='text/javascript'>
														var cselector = document.getElementById('activist_censor_chooser');
														cselector.addEventListener('change', function() {
															var specific = document.getElementById('activist_censor_post');
															specific.checked = 'checked';
															specific.value = cselector.value;
														}, true);
													</script>
												</span>
												</label>
												</p>
												</fieldset>
											</p>
										</td>
									</tr>
                </tbody>
              </table>
            </div>
            <div id="major-publishing-actions">
							<?php wp_nonce_field('activist-config') ?>
							<div id="publishing-action">
								<input type="hidden" name="action" value="update-config">
								<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'activist');?>">
							</div>
							<div class="clear"></div>
						</div>
          </form>
        </div>
      </div>
  </div>

</div>
