if(!notify.isSupported){
        // display message that it will be supported
}
else{
    notify.config({pageVisibility: false, autoClose: 5000});
    if(notify.permissionLevel() != notify.PERMISSION_GRANTED){
    	notify.requestPermission();
    }
}