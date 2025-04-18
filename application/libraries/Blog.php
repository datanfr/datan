<?php

class Blog {
    protected $categories = [
        [
            "id" => 1,
            "name" => "Actualité politique",
            "slug" => "actualite-politique",
            "subtitle" => "Nos analyses sur l'actualité de l'Assemblée nationale",
            "description_meta" => "Découvrez nos analyses approfondies sur l'actualité des députés et de l'Assemblée nationale."
        ],
        [
            "id" => 2,
            "name" => "Datan",
            "slug" => "datan",
            "subtitle" => "Les nouvelles du projet Datan",
            "description_meta" => "Vous voulez tout savoir sur le projet Datan ? Découvrez nos dernières nouvelles dans ce blog."
        ],
        [
            "id" => 3,
            "name" => "Rapports",
            "slug" => "rapports",
            "subtitle" => "Nos études sur l'Assemblée nationale et les députés",
            "description_meta" => "Retrouvez nos études et analyses sur le travail parlementaire."]
    ];

    public function get_categories() {
        return $this->categories;
    }

    public function get_category_by_id($id){
        foreach ($this->categories as $cat) {
            if ($cat["id"] == $id) return $cat;
        }
        return null;
    }

    public function get_category_by_slug($slug){
        foreach ($this->categories as $cat) {
            if ($cat["slug"] == $slug) return $cat;
        }
        return null;
    }
}