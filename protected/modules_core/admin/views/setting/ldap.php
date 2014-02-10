<h1><?php echo Yii::t('AdminModule.base', 'LDAP - Settings'); ?></h1><br>

<ul class="nav nav-pills" id="ldapTabs">
    <li class="active"><a href="#1">Ganz</a></li>
    <li><a href="#2">Viele</a></li>
    <li><a href="#3">LDAP</a></li>
    <li><a href="#4">Einstellungen</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="1">
        <?php echo Yii::t('AdminModule.base', 'Configure LDAP Mapping.'); ?>
    </div>
    <div class="tab-pane active" id="2"></div>
    <div class="tab-pane active" id="3"></div>
    <div class="tab-pane active" id="4"></div>
</div>

<script>
    $('#ldapTabs a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })
</script>