
<form autocomplete="new-password" method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">
<div class="card">
<h1 class="card-header">Set Password</h1>
<div class="card-body">

	<div class="form-group">
		<label>Email:</label>
		<input class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" readonly value="<?= h($data['auth_username']) ?>">
	</div>

	<div class="form-group">
		<label>New Password:</label>
		<input autofocus class="form-control password-input" id="password0" type="password" name="p0" >
		<small class="form-text text-muted" id="password-hint">
			Your password must be at least
			<span id="password-length">8 characters</span>, contain
			<span id="password-upper">UPPER</span> and
			<span id="password-lower">lower</span> cased letters,
			<span id="password-number">numbers</span> and
			<span id="password-symbol">special characters</span>.</small>
	</div>

	<div class="form-group">
		<label>Confirm Password:</label>
		<input class="form-control password-input" id="password1" type="password" name="p1" >
		<small class="form-text text-muted" id="password-hint">Must <span id="password-match">match</span> above entry</small>
	</div>

</div>
<div class="card-footer">
	<button class="btn btn-outline-primary" disabled id="btn-password-update" name="a" type="submit" value="update">Update <i class="icon icon-arrow-right"></i></button>
</div>
</div>
</div>

</form>


<script>
function password_checker(e) {

	var t_good = 'text-success';
	var t_warn = 'text-warning';

	// this is the element being evented on
	var n = this;
	$(n).removeClass('border-success border-warning border-danger');

	var p = n.value;
	var s = 0;
	var x = null;

	$('#password-hint span').removeClass(t_good);

	if (p.length >= 8) {
		s++;
		$('#password-length').removeClass(t_warn).addClass(t_good);
	} else {
		$('#password-length').removeClass(t_good).addClass(t_warn);
	}

	x = $('#password-upper');
	if (p.match(/[A-Z]/)) {
		s++;
		x.removeClass(t_warn).addClass(t_good);
	} else {
		x.removeClass(t_good).addClass(t_warn);
	}

	x = $('#password-lower');
	if (p.match(/[a-z]/)) {
		s++;
		x.removeClass(t_warn).addClass(t_good);
	} else {
		x.removeClass(t_good).addClass(t_warn);
	}

	x = $('#password-number');
	if (p.match(/[0-9]/)) {
		s++;
		x.removeClass(t_warn).addClass(t_good);
	} else {
		x.removeClass(t_good).addClass(t_warn);
	}

	x = $('#password-symbol');
	if (p.match(/[!"\#\$\%\&'\(\)\*\+,\-\.\/:;<=>\?@\[\\\]\^_\{\|\}\~]/)) {
		s++;
		x.removeClass(t_warn).addClass(t_good);
	} else {
		x.removeClass(t_good).addClass(t_warn);
	}

	// $('#auth-password-hint').html(hint);

	if (s >= 3) {
		$(n).addClass('border-success');
	} else if (s >= 2) {
		$(n).addClass('border-warning');
	} else if (s >= 1) {
		$(n).addClass('border-danger');
	}

}


function password_matcher(e)
{
	var p0 = $('#password0').val();
	var p1 = $('#password1').val();

	if (p0 === p1) {
		$('#password-match').addClass('text-success');
		$('#btn-password-update').prop('disabled', false);
		$('#btn-password-update').addClass('btn-primary').removeClass('btn-outline-primary');
	} else {
		$('#password-match').removeClass('text-success');
		$('#btn-password-update').prop('disabled', true);
		$('#btn-password-update').addClass('btn-outline-primary').removeClass('btn-primary');
	}

}

// 	$('.password-input').on('blur keyup', dbpie);

$(function() {
	$('#password0').on('blur change keyup', password_checker);
	$('#password1').on('blur change keyup', password_matcher);
});
</script>
