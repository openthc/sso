{% extends "layout/html.html" %}

{% block body %}

<div class="auth-wrap">
<div class="card">
<h1 class="card-header">{{ Page.title }}</h1>
<div class="card-body">

	<p>All users must verify their email address and phone number to use these services.</p>

	<form method="post">
	<div class="form-group">
		<label>Email Address:</label>
		<div class="input-group">
			{% if verify_email %}
			<div class="input-group-prepend">
				<div class="input-group-text text-danger">
					&otimes;
				</div>
			</div>
			<input autofocus class="form-control" name="contact-email" value="{{ contact_email }}">
			<div class="input-group-append">
				<button class="btn btn-outline-primary" name="a" type="submit" value="email-verify-send">Resend <i class="icon icon-arrow-right"></i></button>
			</div>
			{% else %}
				<div class="input-group-prepend">
					<div class="input-group-text text-success">
						&starf;
					</div>
				</div>
				<input class="form-control" disabled name="contact-email" value="{{ contact_email }}">
			{% endif %}
		</div>
	</div>
	</form>

	<form method="post">
	<div class="form-group">
		<label>Phone Number:</label>
		<div class="input-group">
			{% if verify_phone %}
			<div class="input-group-prepend">
				<div class="input-group-text text-danger">
					&otimes;
				</div>
			</div>
			<input {{ verify_phone_code ?? "autofocus" }} class="form-control" id="contact-phone" inputmode="tel" name="contact-phone" value="{{ contact_phone }}">
			<div class="input-group-append">
				<button class="btn btn-secondary" name="a" type="submit" value="phone-verify-send">Resend <i class="icon icon-arrow-right"></i></button>
				{% if verify_phone_tick > 1 %}
					<button class="btn btn-warning" name="a" type="submit" value="phone-verify-skip">Skip</button>
				{% endif %}
			</div>
			{% else %}
				<div class="input-group-prepend">
					<div class="input-group-text text-success">
						<i class="icon icon-check-circle-o"></i>
					</div>
				</div>
				<input class="form-control" disabled id="contact-phone" inputmode="tel" name="contact-phone" value="{{ contact_phone }}">
			{% endif %}
		</div>
	</div>
	</form>

	{% if verify_phone_warn %}
		<div class="alert alert-warning">{{ verify_phone_warn }}</div>
	{% endif %}

	{% if verify_phone_code %}
	<form method="post">
	<div class="form-group">
		<label>Verification Code:</label>
		<div class="input-group">
			<input autofocus class="form-control" id="phone-verify-code" name="phone-verify-code" value="">
			<div class="input-group-append">
				<button class="btn btn-primary" name="a" type="submit" value="phone-verify-save">Verify</button>
			</div>
		</div>
		<p>You may need to wait a few minutes for the message to arrive. You can resend if needed.</p>
	</div>
	</form>
	{% endif %}

</div>
{% if verify_skip %}
	<div class="card-footer">
		<a class="btn btn-outline-primary" href="/auth/open">Sign In</a>
	</div>
{% endif %}
</div>
</div>

{% endblock %}
