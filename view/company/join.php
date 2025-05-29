<form autocomplete="off" method="post">
<input name="CSRF" type="hidden" value="<?= $data['CSRF'] ?>">

<div class="auth-wrap">
<div class="card">

<h1 class="card-header"><?= $data['Page']['title'] ?></h1>

<div class="card-body">

<div class="mt-4">
        <label>Company Name:</label>
        <input
                autocomplete="off"
                autofocus
                class="form-control"
                id="company-name"
                name="company-name"
                placeholder="Company Name"
                required
                tabindex="1"
                type="text"
                value="">
</div>

<div class="mt-4">
        <label>Government ID:</label>
        <input
                autocomplete="off"
                autofocus
                class="form-control"
                id="company-guid"
                name="company-guid"
                placeholder="Company Government ID"
                required
                tabindex="1"
                type="text"
                value="">
	</div>



</div>
<div class="card-footer">
	<button class="btn btn-primary" name="a" value="company-join-search">Search</button>
</div>
</div>

</div>
</form>


<script>
(function() {

        <?php
        $dir_origin = \OpenTHC\Config::get('openthc/dir/origin');
        if ( ! empty($dir_origin)) {
        ?>
                $('#company-name').autocomplete({
                        source: '<?= $dir_origin ?>/api/autocomplete/company',
                });
        <?php
        }
        ?>
})();
</script>

