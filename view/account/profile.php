{% extends "layout/html.html" %}

{% block body %}

<div class="auth-wrap">

<form method="post">
<div class="card">
<h1 class="card-header">{{ Page.title }}</h1>
<div class="card-body">

	<div class="form-group">
		<label>Name</label>
		<input class="form-control" name="contact-name" type="text" value="{{ Contact_Base.name }}">
	</div>

	<div class="form-group">
		<label>Email / Username</label>
		<input class="form-control" name="contact-email" readonly type="email" value="{{ Contact_Auth.username }}">
	</div>

	<div class="form-group">
		<label>Phone</label>
		<div class="input-group">
			<input class="form-control" name="contact-phone" type="tel" value="{{ Contact_Base.phone }}">
			<div class="input-group-append">
				<button class="btn btn-outline-secondary" name="a" value="contact-phone-update">Change</button>
			</div>
		</div>
	</div>

</div>
<div class="card-footer">
	<button class="btn btn-outline-primary"><i class="fas fa-save"></i> Save</button>
</div>
</div>
</form>

{% if company_list %}
	<hr>
	<div class="card">
		<h2 class="card-header">Company Connections</h2>
		<div class="card-body">
			{% for cp in company_list %}
				<h3>{{ cp.name }}</h3>
			{% endfor %}
		</div>
	</div>
{% endif %}

{% if service_list %}
	<hr>
	<div class="card">
		<h2 class="card-header">Service Connections</h2>
		<div class="card-body">
			{% for s in service_list %}
				<h3><a href="{{ s.link }}" target="_blank">{{ s.name }}</a></h3>
			{% endfor %}
		</div>
	</div>
{% endif %}


</div>



{% endblock %}
