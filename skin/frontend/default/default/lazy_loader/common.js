//$.noConflict();
jQuery(document).ready(function(jQuery) {
  jQuery('img.lazy').jail({
    event: 'load+scroll',
    placeholder : "../../skin/frontend/default/default/images/lazy_loader/loader.gif",
  });
});