<?php

namespace App\Http\Controllers\BasController;

use Illuminate\Http\Request;

interface BaseControllerInterface
{
    public function model();
    public function getSort();
    public function getRelations();
    public function getSearchableFields();
    public function indexPaginat();
    public function getFilters();
    public function storeDefaultValues();
    public function uploadImages();
    public function index(Request $request);
    public function store(Request $request);
    public function show(int $id);
    public function update(int $id, Request $request);
    public function delete(int $id);
}