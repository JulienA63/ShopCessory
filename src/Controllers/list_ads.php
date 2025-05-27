<?php
require '../../config/db.php';
require '../Models/ad_model.php';

$ads = get_all_ads();
include '../Views/list_ads.php';
