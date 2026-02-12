<?php

trait WAPT_ImagePerPage {

    protected $per_page = 20;

    public function set_per_page( $per_page ) {
        $this->per_page = $per_page;
        return $this;
    }

}
