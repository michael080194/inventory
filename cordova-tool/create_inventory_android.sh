#!/bin/bash
# create by Michael 2020-02-22
 
read -p "Enter project fullname: " fullname
 
 
if [ ! -d "$fullname" ] ; then
   echo $fullname
else
     echo $fullname " this project already exist "
     exit 1
fi
 
sudo cordova create $fullname com.kyc168.$fullname $fullname
 
sudo chmod 777 $fullname
 
 
dir=$(pwd -P)"/"$fullname
pushd "$dir" > /dev/null
# sudo cordova platform add android
# sudo cordova platform add android@latest
sudo cordova platform add android@7

sudo cordova plugin add  cordova-plugin-image-picker@latest
sudo cordova plugin add  phonegap-plugin-barcodescanner
sudo cordova plugin add  cordova-plugin-camera
sudo cordova plugin add  cordova-plugin-file@latest
sudo cordova plugin add  cordova-plugin-file-transfer
sudo cordova plugin add  cordova-plugin-network-information
sudo cordova plugin add  cordova-plugin-dialogs
sudo cordova plugin add  cordova-plugin-device
sudo cordova plugin add  cordova-plugin-inappbrowser@latest
sudo cordova plugin add  cordova-sqlite-storage@latest
sudo cordova plugin add  cordova-android-support-gradle-release  --variable ANDROID_SUPPORT_VERSION=27+
sudo cordova plugin add  nl.codeyellow.signature
sudo cordova plugin add  cordova-plugin-screen-orientation
sudo cordova prepare android
# classpath 'com.google.gms:google-services:4.0.1'
# implementation 'com.google.firebase:firebase-core:16.0.1'
# apply plugin: 'com.google.gms.google-services'