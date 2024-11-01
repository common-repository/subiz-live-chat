<?php
$settings = get_option('subiz_settings')
?>
<div class="subiz_card">
  <img class="subiz_logo" width="80" src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'subiz-live-chat/images/logo.svg'; ?>">

  <div class="subiz_header" id="subiz_header">
<?php echo !empty($settings['subiz_account_id']) ? 'Cài đặt cửa sổ chat' : 'Thêm cửa sổ chat vào Subiz'; ?>

</div>

<?php
    if (!empty($settings['subiz_account_id']) && $settings['subiz_widget_show'] == 1) {
        echo '<div class="subiz_text_success"><div style="border-radius: 4px; width: 10px; height: 10px; background: #19b600; display: inline-block"></div>&nbsp;&nbsp;<b>Đang hiện</b></div>';
    } else {
        echo '<div class="subiz_text_muted"><div style="border-radius: 4px; width: 10px; height: 10px; background: gray; display: inline-block"></div>&nbsp;&nbsp;Đang tắt</div>';
    }
?>

  <div  id="subiz_subtitle">
<?php if (empty($settings['subiz_account_id'])) {
    echo '<div class="subiz_text_muted">Nhập <a href="https://app.subiz.com.vn/settings/" target="_blank">ID tài khoản</a> của bạn ở bên dưới rồi nhấp nút Cài đặt để cài đặt cửa sổ chat lên website của bạn</div>';
} ?>
<?php if (!empty($settings['subiz_account_id'])) {
    echo '
<div class="subiz_subtitle"><b>Thông tin tài khoản</b></div>
  <div><span class="subiz_text_muted">Account ID:</span> <span>' .$settings['subiz_account_id'] . '</span></div>
  <div><span class="subiz_text_muted">Tên tài khoản:</span> <span id="acc_name_result"></span></div>
';
} ?>
  </div>
  <form id="subiz_settings_form" method="post" action="options.php">
<?php
settings_fields('subiz_options');
do_settings_sections('subiz_options');
?>
<?php
if (empty($settings['subiz_account_id'])) {
    echo '
    <div class="subiz_d_flex subiz_w_100">
      <input type="text" id="acc_id_input" name="subiz_settings[subiz_account_id]" class="subiz_input" placeholder="Account ID">
      <input type="hidden" name="subiz_settings[subiz_widget_show]" value="1">
      <button type="button" class="subiz_btn primary" disabled id="submit_btn" style="margin-left: 12px; width: 120px">Cài đặt</button>
    </div>
    <div class="subiz_submit_result" id="subiz_submit_result">
      &nbsp;
    </div>

';
}
?>
<?php
if (!empty($settings['subiz_account_id'])) {
    echo '
<div class="subiz_subtitle"><b>Tùy chọn</b></div>
<div id="form_inner">

<div class="subiz_box">
  <div class="subiz_box__header">
	  <label class="subiz_switch" for="subiz_widget_show_cb">
      <input
        type="checkbox"
				class="slider round"
				id="subiz_widget_show_cb"
				name="subiz_settings[subiz_widget_show]"
				value="1"
				' . checked(1, $settings['subiz_widget_show'], false) . ' />
			<div class="subiz_switch_slider"></div>
		</label>
    <div style="font-size: 16px; margin-left: 10px;">Hiển thị cửa sổ chat</div>
</div>
</div>';
    include 'contactform7.php';
    echo '<input type="hidden" name="subiz_settings[subiz_account_id]" value="' .$settings['subiz_account_id'] .'">
<div style="margin-top: 120px; font-size: 16px">
  <div><a href="https://app.subiz.com.vn/" target="_blank">Xem danh sách tin nhắn</a></div>
  <div><a href="https://app.subiz.com.vn/chatbox/design" target="_blank">Cài đặt cửa sổ chat</a></div>
<div><a href="javascript:;" id="change_acc_btn">Đổi tài khoản</a></div>
  <div><a href="https://subiz.com.vn/vi/contact.html" target="_blank">Trợ giúp</a></div>
</div>
</div>';
}
?>
    <div class="subiz_version">Phiên bản 4.5</div>
  </form>
</div>

<script>
let states = {
  initAccId: '<?php echo $settings['subiz_account_id'] ?>',
  subiz_widget_show: '<?php echo $settings['subiz_widget_show'] ?>',
  accInfo: {},
}
let _hasClickEdit = false

if (states.initAccId) {
  getAccountInfo()
}

function displayAccName () {
  let $acc_name_result = document.getElementById('acc_name_result')
  if ($acc_name_result) $acc_name_result.textContent = states.accInfo.name || ''
}

function debounce(func, timeout = 300){
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
    func.apply(this, args);
    }, timeout);
  };
}

function throttle(mainFunction, delay) {
    let timerFlag = null; // Variable to keep track of the timer

    // Returning a throttled version
    return (...args) => {
        if (timerFlag === null) { // If there is no timer currently running
            mainFunction(...args); // Execute the main function
            timerFlag = setTimeout(() => { // Set a timer to clear the timerFlag after the specified delay
                    timerFlag = null; // Clear the timerFlag to allow the main function to be executed again
                }, delay);
        }
    };
}

async function getAccountInfo () {
  let accid = states.initAccId
  try {
    let res = await fetch(`https://api.subiz.com.vn/4.0/accounts/${accid}/`)
    res = await res.json()
    states.accInfo = res
    displayAccName()
  } catch (error) {
    console.error(error)
  }
}

var sleep = (ms) => new Promise((res) => setTimeout(res, ms))

async function checkAccId () {
  displayResult('muted', 'Đang kiểm tra...')
  let accid = states.initAccId
  try {
    let res = await fetch(`https://api.subiz.com.vn/4.0/accounts/${accid}/`)
        await sleep(500)
    if (!res.ok) {
      displayResult('danger', 'Lỗi mạng, không thể lưu')
    }
    res = await res.json()
    if (res.error) {
      displayResult('danger', 'Tài khoản không hợp lệ')
    } else {
      displayResult('success', `Tài khoản hợp lệ - ${res.name}`)
    }
  } catch (error) {
    displayResult('danger', 'Đã có lỗi xảy ra, vui lòng thử lại')
  }
}

const debounceCheckAccId = throttle(() => checkAccId(), 800)

let $btn = document.getElementById('submit_btn')
if ($btn) {
  $btn.addEventListener('click', () => {
    let $form = document.getElementById('subiz_settings_form')
    if ($form) $form.submit()
  })
}

let $acc_id_input = document.getElementById('acc_id_input')
if ($acc_id_input) {
  $acc_id_input.addEventListener('input', e => {
    states.initAccId = e.target.value
    debounceCheckAccId()
  })
}

let $change_acc_btn = document.getElementById('change_acc_btn')
$change_acc_btn.addEventListener('click',  () => {
  changeHeaderTitleInEditMode()
  changeSubtitleInEditMode()
  changeFormInEditMode()
  registerEventsInEditMode()
  _hasClickEdit = true
})

function changeHeaderTitleInEditMode () {
  let $header = document.getElementById('subiz_header')
  if ($header) $header.textContent = 'Đổi tài khoản'
}

function changeSubtitleInEditMode () {
  let $subtitle = document.getElementById('subiz_subtitle')
  if ($subtitle) $subtitle.innerHTML = '<div class="subiz_text_muted">Nhập ID tài khoản mới bên dưới</div>'
}

function changeFormInEditMode() {
  let $form = document.getElementById('form_inner')
  if ($form) {
    $form.innerHTML = `
      <div class="subiz_d_flex subiz_w_100">
        <input type="text" id="acc_id_input" name="subiz_settings[subiz_account_id]" class="subiz_input" placeholder="Account ID" value="">
        <input type="hidden" name="subiz_settings[subiz_widget_show]" value="${states.subiz_widget_show}">
        <button type="button" class="subiz_btn primary" disabled id="submit_btn" style="margin-left: 12px; width: 120px">Cập nhật</button>
      </div>
      <div class="subiz_submit_result" id="subiz_submit_result">
        &nbsp;
      </div>
      <div style="margin-top: 8px">
        <button type="button" class="subiz_btn light" onclick="location.reload()">Quay lại</button>
      </div>
      `
  }
}

let $checkbox = document.getElementById('subiz_widget_show_cb')
if ($checkbox) {
  $checkbox.addEventListener('change', () => {
    let $form = document.getElementById('subiz_settings_form')
    if ($form) $form.submit()
  })
}

function registerEventsInEditMode() {
  if (_hasClickEdit) return
  let $btn = document.getElementById('submit_btn')
  if ($btn) {
    $btn.addEventListener('click', () => {
      let $form = document.getElementById('subiz_settings_form')
      if ($form) $form.submit()
    })
  }

  let $acc_id_input = document.getElementById('acc_id_input')
  if ($acc_id_input) {
    $acc_id_input.addEventListener('input', e => {
      states.initAccId = e.target.value
      debounceCheckAccId()
    })
  }
}

function displayResult(type, message) {
    if (type == 'success')  $btn.disabled = false
        else $btn.disabled = true
  let $result = document.getElementById('subiz_submit_result')
  $result.innerHTML = `<div class="subiz_text_${type}">${message}</div>`
}
</script>
