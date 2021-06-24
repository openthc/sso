{% extends "layout/html.html" %}

{% block body %}

<form method="post">
<div class="auth-wrap">
<div class="card">

<h1 class="card-header">{{ Page.title }}</h1>

<div class="card-body">

	{{ auth_hint|raw }}

	<div class="form-group">
		<label>Email</label>
		<input class="form-control" id="username" inputmode="email" name="username" placeholder="- user@example.com -" value="{{ auth_username }}">
	</div>

	<div class="form-group">
		<div class="g-recaptcha" data-sitekey="{{ Google.recaptcha_public }}"></div>
	</div>

</div>

<div class="card-footer">
	<div class="row">
		<div class="col-md-6">
			<button class="btn btn-success" name="a" type="submit" value="password-reset-request">Request Password Reset</button>
		</div>
	</div>
</div>

</div>
</div>
</form>

{% endblock %}

{% block foot_script %}
{{ parent() }}
<script src="https://www.google.com/recaptcha/api.js"></script>
{% endblock %}
