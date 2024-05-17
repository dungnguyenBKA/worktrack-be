<?php


namespace App\Http\Requests\Cms;


class WorkTitleRequest extends CmsBaseRequest
{
    public function rules()
    {
        $workTitle = $this->route()->parameter('work_title');
        $id = $workTitle ? $workTitle->id : null;
        return [
            'name_en' => 'required|string|max:50|unique:work_titles,name_en,'.$id.',id,deleted_at,NULL',
            'name_vi' => 'nullable|string|max:50',
            'name_ja' => 'nullable|string|max:50',
        ];
    }
}
