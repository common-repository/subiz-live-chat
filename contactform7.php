<?php
$settings = get_option('subiz_settings')
?>

<div class="subiz_box">
  <div class="subiz_box__header">
		<label class="subiz_switch" for="subiz_widget_cf7_cb">
			<input
				type="checkbox"
				class="slider round subiz_widget_cf7_form_control"
				id="subiz_widget_cf7_cb"
				value="1"
				name="subiz_settings[subiz_cf7]"
				<?php echo checked(1, $settings['subiz_cf7'], false); ?>
				/>
			<div class="subiz_switch_slider"></div>
		</label>
		<div style="font-size: 16px; margin-left: 10px;">Đồng bộ Contact Form 7</div>

	</div>
	<div class="subiz_text_muted" style="margin-left: 38px">Chuyển dữ liệu khách hàng nhập từ plugin Contact Form 7 về Subiz</div>
	<?php
    $formscls = '';
if ($settings['subiz_cf7'] == 1) {
    $formscls = 'subiz_cf7_forms__enabled';
}

?>
	<div class="subiz_cf7_forms <?= $formscls ?>">
	<?php $posts = get_posts(array('post_type' => 'wpcf7_contact_form','numberposts' => -1));
foreach ($posts as $p) {

    $lastsync = get_option('subiz_wpcf7_' . $p->ID .'_last_submit_at');
    if ($lastsync > 0) {
        $lastsync = 'Gửi lần cuối vào '. date('h:i:s d/m/Y', $lastsync / 1000);
    } else {
        $lastsync = 'Chưa từng đồng bộ';
    }

    $cls = 'subiz_cf7_form__disabled';
    if (get_option('subiz_wpcf7_' .$p->ID . 'enabled') == 1) {
        $cls = 'subiz_cf7_form__enabled';
    };
    ?>
	<div style="margin-left: 38px" >
		<div class="subiz_cf7_form <?= $cls ?>" style="margin-top: 10px; margin-bottom: 10px">
			<label class="subiz_switch" for="subiz_widget_cf7_form_<?= $p->ID ?>" style="margin-top: 4px">
				<input
					type="checkbox"
					class="slider round subiz_widget_cf7_form_control"
					id="subiz_widget_cf7_form_<?= $p->ID ?>"
					value="1"
					name="subiz_settings[subiz_wpcf7_<?= $p->ID ?>_enabled]"
					<?php echo checked(1, $settings['subiz_wpcf7_' . $p->ID . '_enabled'], false); ?>
					/>
					<div class="subiz_switch_slider subiz_switch_slider__sm"></div>
			</label>

			<div style="margin-left: 10px;">
				<div class="subiz_form_title"><?= $p->post_title ?>&nbsp;<a class="subiz_cf7_form_link" href="/wp-admin/admin.php?page=wpcf7&post=<?= $p->ID ?>&action=edit" target="_blank">Chỉnh sửa</a></div>
				<div class="subiz_text_muted">
					<?= $lastsync ?>
				</div>
			</div>
		</div>
	</div>
	<?php
} ?>
	</div>
</div>
	<script>

	  window.addEventListener("load", function () {
	  let $checkboxs = document.getElementsByClassName("subiz_widget_cf7_form_control");
		for (let $checkbox of $checkboxs) {
		console.log("TTTTTTTTTT", $checkbox)
    $checkbox.addEventListener("change", (e) => {
    let $form = document.getElementById('subiz_settings_form')
    if ($form) $form.submit()
		console.log("EEEEEEEE", $form, e.target.value)

    });
		}
		});

	</script>
