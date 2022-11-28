<?php

if(is_file("/tmp/energy_scrape_file")) {
    echo file_get_contents("/tmp/energy_scrape_file");
}