/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.initModule('client.upload', function (module, require, $) {
    var initConfig = function(cfg) {
        var defaultCfg =  {
            dropZone: $(this),
            url: cfg.url  || module.config['default.url'],
            dataType: 'json',
            singleFileUploads: true,
            limitMultiFileUploads: 1
        };
        
        return $.extend(defaultCfg, cfg);
    };
    
    var set = function(target, cfg) {
        var cfg = cfg || {};
        
        if(target instanceof $.Event) {
            cfg.url = target.url;
            target = target.$target;
        }
        
        cfg = initConfig(cfg);
        
        var $target = $(target);
        
        
        
    };
    
    /*/ If no url is given we set the default file upload url or use the data-url of blueimp
    <button data-action-click="client.upload" data-action-target="#asdf" data-action-url="<?= asdf ?>"  /> 
    
    <form>
        <input id="#asdf" name="files[]" type="file" 
                data-upload-single="true" 
                data-upload-dropzone="#asdf" 
                data-upload-progress="#myprogressbar" 
                data-action-done="myModule.uploadFinished" />
    </form>
    
    /humhub/widgets/FilePreview::widget([bind=>"#asdf"]);
    
    /humhub/widgets/ProgressBar::widget([id=>"#myProgressbar"]);
*/
    module.export({
        init:init
    });
});

/**
 * 
 var handleResponse = function (json, callback) {
 var response = new Response(json);
 if (json.content) {
 response.$content = $('<div>' + json.content + '</div>');
 
 //Find all remote scripts and remove them from the partial
 var scriptSrcArr = [];
 response.$content.find('script[src]').each(function () {
 scriptSrcArr.push($(this).attr('src'));
 $(this).remove();
 });
 
 //Load the remote scripts synchronously only if they are not already loaded.
 scripts.loadOnceSync(scriptSrcArr, function () {
 callback(response);
 });
 } else {
 callback(response);
 }
 };
 */