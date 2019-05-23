<?php

namespace abc\controllers\admin;

use abc\core\engine\AControllerAPI;
use abc\models\catalog\Category;
use abc\models\catalog\ResourceLibrary;

class ControllerApiCatalogCategory extends AControllerAPI
{
    public function get()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $request = $this->rest->getRequestParams();
        $this->data['request'] = $request;

        $getBy = null;
        if (isset($request['category_id']) && $request['category_id']) {
            $getBy = 'category_id';
        }
        if (isset($request['get_by']) && $request['get_by']) {
            $getBy = $request['get_by'];
        }

        if (!\H::has_value($getBy) || !isset($request[$getBy])) {
            $this->rest->setResponseData(['Error' => $getBy.' is missing']);
            $this->rest->sendResponse(200);
            return null;
        }

        if ($getBy !== 'pathTree') {
            $category = Category::where($getBy, $request[$getBy])->get()->first();
        } else {
            $this->load->model('catalog/category');
            $languageId = $this->language->getLanguageCodeByLocale('en');
            $categories = Category::withTrashed()->get();

            foreach ($categories as $findcategory) {
                $pathTree = $this->model_catalog_category->getPath($findcategory->category_id, $languageId, '');
                if ($pathTree == $request[$getBy]) {
                    $category = $findcategory;
                    break;
                }
            }
        }

        if ($category === null) {
            $this->rest->setResponseData(
                [
                    'Error'        => "Category with ".$getBy." ".htmlspecialchars_decode($request[$getBy])." does not exist",
                    'error_status' => 0,
                ]
            );
            $this->rest->sendResponse(200);
            return null;
        }

        $this->data['result'] = [];
        /**
         * @var Category $item
         */
        $item = $category;
        if ($item) {
            $this->data['result'] = $item->getAllData();
        }
        if (!$this->data['result']) {
            $this->data['result'] = [
                'Error'        => 'Requested Category Not Found',
                'error_status' => 0,
            ];
        }

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($this->data['result']);
        $this->rest->sendResponse(200);
    }

    public function put()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        try {
            $request = $this->rest->getRequestParams();
            $this->data['request'] = $request;

            if (!is_array($this->data['request'])) {
                $this->rest->setResponseData(['Error' => 'Not correct input data']);
                $this->rest->sendResponse(200);
                return null;
            }

            $category = (new Category())->addCategory($this->data['request']);
            if ($category) {
                $this->data['result']['category_id'] = $category;
                if (isset($this->data['request']['category_images'])) {
                    $categoryImages['images'] = $this->data['request']['category_images'];
                    $resource_mdl = new ResourceLibrary();
                    $resource_mdl->updateImageResourcesByUrls($categoryImages,
                        'categories',
                        $category,
                        '',
                        $this->language->getContentLanguageID());
                }
                if (isset($this->data['request']['parent_uuid'])) {
                    $parentCategory = Category::where('uuid', '=', $this->data['request']['parent_uuid'])
                        ->get()
                        ->first();
                    $categoryObj = Category::find($category);
                    if ($parentCategory && $categoryObj) {
                        $categoryObj->parent_id = $parentCategory->category_id;
                        $categoryObj->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->rest->setResponseData(['Error' => 'Create Error: '.$e->getMessage()]);
            $this->rest->sendResponse(200);
            return null;
        }

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($this->data['result']);
        $this->rest->sendResponse(200);
    }

    public function post()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        $request = $this->rest->getRequestParams();
        try {

            $this->data['request'] = $request;

            //are we updating
            $updateBy = null;
            if (isset($request['category_id']) && $request['category_id']) {
                $updateBy = 'category_id';
            }
            if (isset($request['update_by']) && $request['update_by']) {
                $updateBy = $request['update_by'];
            }

            if ($updateBy) {

                if ($updateBy !== 'pathTree') {
                    $category = Category::where($updateBy, $request[$updateBy])->first();
                } else {
                    $this->load->model('catalog/category');
                    $languageId = $this->language->getLanguageCodeByLocale('en');
                    $categories = Category::withTrashed()->get();

                    foreach ($categories as $findcategory) {
                        $pathTree = $this->model_catalog_category->getPath($findcategory->category_id, $languageId, '');
                        if ($pathTree === $request[$updateBy]) {
                            $category = $findcategory;
                            break;
                        }
                    }
                }

                if ($category === null) {
                    $this->rest->setResponseData(
                        ['Error' => "Category with {$updateBy}: {$request[$updateBy]} does not exist"]
                    );
                    $this->rest->sendResponse(200);
                    return null;
                }

                (new Category())->editCategory($category->category_id, $request);

                if (isset($request['category_images'])) {
                    $categoryImages['images'] = $request['category_images'];
                    $resource_mdl = new ResourceLibrary();
                    $resource_mdl->updateImageResourcesByUrls($categoryImages,
                        'categories',
                        $category->category_id,
                        '',
                        $this->language->getContentLanguageID());
                }
                if (isset($request['parent_uuid'])) {
                    $parentCategory = Category::where('uuid', '=', $request['parent_uuid'])
                        ->get()
                        ->first();
                    if ($parentCategory) {
                        $category->parent_id = $parentCategory->category_id;
                        $category->save();
                    }
                }
            }
        } catch (\PDOException $e) {
            $trace = $e->getTraceAsString();
            $this->log->error($e->getMessage());
            $this->log->error($trace);
            $this->rest->setResponseData(['Error' => $e->getMessage()]);
            $this->rest->sendResponse(200);
            return null;
        } catch (AException $e) {
            $this->rest->setResponseData(['Error' => $e->getMessage()]);
            $this->rest->sendResponse(200);
            return null;
        }

        $this->data['result'] = [
            'status'      => $updateBy ? 'updated' : 'created',
            'category_id' => $category->category_id,
        ];

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($this->data['result']);
        $this->rest->sendResponse(200);
    }

    public function delete()
    {
        $this->extensions->hk_InitData($this, __FUNCTION__);

        try {
            $request = $this->rest->getRequestParams();
            $this->data['request'] = $request;

            //are we updating
            $deleteBy = null;
            if (isset($request['category_id']) && $request['category_id']) {
                $deleteBy = 'category_id';
            }
            if (isset($request['delete_by']) && $request['delete_by']) {
                $deleteBy = $request['delete_by'];
            }

            if ($deleteBy) {
                Category::where($deleteBy, $request[$deleteBy])
                    ->delete();
            } else {
                $this->rest->setResponseData(['Error' => 'Not correct request, Category_ID not found']);
                $this->rest->sendResponse(200);
                return null;
            }

        } catch (\Exception $e) {

        }

        $this->extensions->hk_UpdateData($this, __FUNCTION__);

        $this->rest->setResponseData($this->data['result']);
        $this->rest->sendResponse(200);
    }

}