if(!notify.isSupported){
        // display message that it will be supported
}
else{
    notify.config({pageVisibility: false, autoClose: 2000});
    if(notify.permissionLevel() != notify.PERMISSION_GRANTED){
    	notify.requestPermission();
    }
}