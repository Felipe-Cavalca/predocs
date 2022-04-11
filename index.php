<?php

include("install/varsInstall.php");

if($_INSTALL["instalou"]){
    include ("app/index.html");
}else{
    include("install/index.php");
}