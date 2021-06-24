{% extends "layout/html.html" %}

{% block body %}
<form autocomplete="off" method="post">
<div class="auth-wrap">

	<div class="card">
	<h1 class="card-header">{{ Page.title }}</h1>
	<div class="card-body">

		<div class="form-group">
			<label>Region:</labeL>
			<input autocomplete="off" class="form-control" id="contact-region" name="contact-iso3166" type="text" value="{{ region.iso3166_1.name }}">
			<input autocomplete="off" class="form-control" id="contact-region" type="hidden" value="{{ region.iso3166_1.code }}">
		</div>

		<!-- <div class="form-group">
			<label>Time Zone:</labeL>
				<div class="input-group">
					<input autocomplete="off" class="form-control" id="contact-time-zone" list="time-zone-list" name="contat-tz" type="text" value="{{ time_zone_pick }}">
					<div class="input-group-append">
						<button class="btn btn-outline-secondary" id="btn-timezone-pick" type="button">PICK</button>
					</div>
				</div>
			<datalist id="time-zone-list">
				{% for tz in time_zone_list %}
					<option value="{{ tz }}"></option>
				{% endfor %}
			</datalist>
		</div> -->


	</div>
	<div class="card-footer r">
		<button class="btn btn-primary" name="a" type="submit" value="save-next">
			Save &amp; Next
			<i class="icon icon-arrow-right"></i>
		</button>
	</div>
	</div>
</div>
</form>
{% endblock %}


{% block foot_script %}
<script>
function _time_zone_pick()
{
	var btn = document.querySelector('#btn-timezone-pick');
	btn.addEventListener('click', function(e) {
		var txt = document.querySelector('#contact-time-zone');
		var tz0 = Intl.DateTimeFormat().resolvedOptions().timeZone;
		txt.value = tz0;
	});

}
_time_zone_pick();
</script>
{% endblock %}
