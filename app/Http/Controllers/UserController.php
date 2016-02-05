<?php namespace App\Http\Controllers;

use SEO;
use Auth;
use Input;
use Image;
use App\CV;
use App\Job;
use App\User;
use App\Comment;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class UserController extends Controller
{

    /*
    *	Get user info by their name
    *	@param string $name to compare with the source
    *	return manage page for user
    */
    public function userDetail($name, $token)
    {
        if (Auth::guest()) {
            return redirect()->intended('/');
        }

        $data['userDetail'] = User::whereRaw('username = ? and remember_token = ? ', [$name, $token])->first();
        if (!$data['userDetail']) {
            return view('errors.404');
        }
        SEO::setTitle('Quản lý tài khoản-' . $data['userDetail']->full_name);
        SEO::setDescription('Quản lý tài khoản,cập nhật ảnh đại diện,xem sửa xóa báo giá,tạo cv....');
        SEO::opengraph()->setUrl('http://localhost:8000/' . $name . '/' . $token);
        $data['username']=Auth::user()->username;
        $data['job_comment_list'] = Comment::where('user_id', '=', Auth::user()->id)->get();
        $data['list_cv']=CV::where('user_id','=',Auth::user()->id)->get();
        return view('ui.userinfo.uinfo', $data);
    }

    /**
     * [userDetailAjax load comment using ajax]
     * @return response
     */
    public function userDetailAjax()
    {
        $job_comment_list = Comment::where('user_id', '=', Auth::user()->id)->paginate(4);
        return view('ui.userinfo.ajax', compact('job_comment_list'));
    }

    /*
    *	Get user info by their name
    *	@param string $name to compare with the source
    *	@var newFileName create uniqe file name base on id
    *  	if directory doesn't exist create directory
    *	return manage page for user
    */
    public function profileImage($name)
    {
        if (Auth::guest()) {
            return redirect()->intended('/');
        }
        $user = new User();
        $userDetail = User::where('username', '=', $name)->first();
        $temp = $_FILES['file']['tmp_name'];
        $image = $_FILES['file']['name'];
        $ext = pathinfo($image, PATHINFO_EXTENSION);

        $newFileName = uniqid($userDetail->id);
        if (!is_dir("images/$name/")) {
            mkdir("images/$name", 0777);
        }
        $img = Image::make($temp);
        $img->fit(150, 150);
        $src = "images/$name/$newFileName.$ext";

        $img->save($src);
        $user = $userDetail;
        $user->avatar = $src;
        $user->save();
        return $src;
    }

    /**
     * [DeleteComment using ajax]
     * @param $id comment to compare with source
     */
    public function deleteComment($id)
    {
        if (Auth::guest()) {
            return redirect()->intended('/');
        }
        Comment::where('id', '=', $id)->delete();
        return response()->json(array('mess' => 'Xóa thành công'));
    }

    /**
     * [jobUserPost get the job that user post]
     * @return [type] [description]
     */
    public function jobUserPost()
    {
        if (Auth::guest()) {
            return redirect()->intended('/');
        }
        $data['jobpost'] = Job::where('user_id', '=', Auth::user()->id)->paginate(4);
        return view('ui.userinfo.uinfo',$data);
    }

    /**
     * [createCV return new create cv view]
     * @return [type] [description]
     */
    public function createCV()
    {
        if (Auth::guest()) {
            return redirect()->intended('/');
        }
        SEO::setTitle('Tạo mới CV');
        return view('ui.userinfo.cv');
    }

    /**
     * [FunctionName save cv via ajax]
     * 
     */
    public function saveCV()
    {
        $cv=new CV();
        $cv->name=Input::get('name');
        $cv->job_name=Input::get('job_name');
        $cv->phone=Input::get('phone');
        $cv->email=Input::get('email');
        $cv->address=Input::get('address');
        $cv->experience=Input::get('experience');
        $cv->education=Input::get('education');
        $cv->activities=Input::get('activities');
        $cv->capabilities=Input::get('capabilities');
        $cv->interests=Input::get('interests');
        $cv->user_id= Auth::user()->id;
        $cv->save();
        return response()->json(array('mess' => 'Lưu thành công'));
    }

    /**
     * [loadCV load cv]
     * @param  [type] $name [name of user]
     * @param  [type] $id   [id of cv]
     * @return [type]       [description]
     */
    public function loadCV($id)
    {
        $data['cv_info']=CV::where('id','=',$id)->first();
        SEO::setTitle('Xem CV của ' . Auth::user()->username);
        return view('ui.userinfo.viewCV',$data);
    }
}
