<?php


namespace App\Http\Requests\Cms;


class RoleRequest extends CmsBaseRequest
{
    public function rules()
    {
        $role = $this->route()->parameter('role');
        $id = $role ? $role->id : null;
        return [
            'name_en' => 'required|string|max:50|unique:roles,name_en,'.$id.',id,deleted_at,NULL',
            'name_vi' => 'nullable|string|max:50',
            'name_ja' => 'nullable|string|max:50',
        ];
    }
}
