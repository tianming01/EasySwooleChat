<?php


namespace App\HttpController;



class Upload extends Base
{
    // 上传图片
    public function image()
    {
        $request = $this->request();
        $img_file = $request->getUploadedFile('file');

        if (!$img_file) {
            $this->writeJson(500, '请选择上传的文件');
        }

        if ($img_file->getSize() > 1024 * 1024 * 5) {
            $this->writeJson(500, '图片不能大于5M！');
        }

        $MediaType = explode("/", $img_file->getClientMediaType());
        $MediaType = $MediaType[1] ?? "";
        if (!in_array($MediaType, ['png', 'jpg', 'gif', 'jpeg', 'pem', 'ico'])) {
            $this->writeJson(500, '文件类型不正确！');
        }

        $path =  '/Static/upload/';
        $dir =  EASYSWOOLE_ROOT.'/Static/upload/';
        $fileName = uniqid().$img_file->getClientFileName();

        if(!is_dir($dir)) {
            mkdir($dir, 0777 , true);
        }

        $flag = $img_file->moveTo($dir.$fileName);

        $data = [
            'name' => $fileName,
            'src' => $path.$fileName,
        ];

        if($flag) {
            $this->writeJson(0, '上传成功', $data);
        } else {
            $this->writeJson(500, '上传失败');
        }
    }
}