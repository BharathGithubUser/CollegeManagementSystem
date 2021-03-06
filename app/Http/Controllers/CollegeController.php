<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\signup;
use App\student_details;
class CollegeController extends Controller
{
    public function index(){
    	return view('home');
    }

    public function sign_up(){
    	return view('sign_up');
    }

    public function login(){
    	return view('login');
    }

    public function admin(){
        $student_details=student_details::Paginate(10);
        return view('display_admin')->with('student_details',$student_details);
    }

    public function search(Request $post_data){
        $search=$post_data->search;
        $user=$post_data->user;
        if($user=="Admin")
        {
            $student_details=student_details::where('name','like','%'.$search.'%')->paginate(2);
            return view('search_results_admin')->with('student_details',$student_details)->with('user',$user);
        }
        else{
            $student_details=student_details::where('name','like','%'.$search.'%')->paginate(2);
            return view('search_results_manager')->with('student_details',$student_details)->with('user',$user);
        }
    }

    public function manager(){
        $student_details=student_details::all();
        return view('display_manager')->with('student_details',$student_details);

    }

    public function post_login(Request $post_data){
        $errors=$this->validate($post_data,['email'=>'required','password'=>'required']);

        if($errors)
         return back();

        else{
            $signup_details=signup::where('email',$post_data->email)->first();
            if($signup_details)
            {
                if($signup_details->password==$post_data->password) {
                    $auth="";
                    $auth=$signup_details->user_type;
                    if($auth=="Admin"){
                        return redirect('/admin');
                    }
                    else if($auth=="Manager"){
                        return redirect('/manager');
                    }
                }
                else
                    return back()->with('invalid_password','Invalid Password');
            }
            else
                return back()->with('invalid_password',"Invalid Email");
        }
    }

    public function enter_student_details(){
    	return view('enter_student_details');
    }

    public function post_sign_up(Request $post_data){
        $errors=$this->validate($post_data,['name'=>'required','email'=>'required','password'=>'required','confirm_password'=>'required|same:password','user_type'=>'required']);
        if($errors)
            return back();
        else{ 
            $signup=new signup;
            $signup->name=$post_data->name;
            $signup->email=$post_data->email;
            $signup->password=$post_data->password;
            $signup->user_type=$post_data->user_type;
            $signup->save();
            echo "Successfully Created the Profile<br>";
            echo "<a href='/login'>Click here to Login</a>";
        }
    }

    public function status(){
        echo "<h4> You Successfully Created Student Record </h4><br>";
        echo "<a href='/enter_student_details'>Click Here to Enter More Records";
    }

    public function post_student_details(Request $post_data){
        $errors=$this->validate($post_data,['name'=>'required','dob'=>'required','gender'=>'required','skills'=>'required','image'=>'required','description'=>'required']);

        if($errors)
            return back();

        else{ 
            $baseUrl="http://localhost/CollegeManagement/public/images/";
            $imageName = time().'.'.$post_data->image->getClientOriginalExtension();//Getting the Image Name.
            $post_data->image->move(public_path('images'), $imageName);//Moving the Image file from /tmp/phpd...to images directory
            $imageUrl=$baseUrl.$imageName;
            $student_details=new student_details;
            $student_details->name=$post_data->name;
            $student_details->dob=$post_data->dob;
            $student_details->age=$post_data->age;
            $student_details->gender=$post_data->gender;
            $skills=$post_data->skills;
            foreach ($skills as $skills_details) {
                $student_details->skills.=$skills_details.",";
            }
            $student_details->image=$imageUrl;
            $student_details->description=$post_data->description;
            $student_details->save();
            return redirect('/status');
        }
    }

    public function edit_student($id){
            $student_details=student_details::find($id);
            return view('edit_student')->with('student_details',$student_details);
    }

    public function post_edit_student(Request $post_data,$id){
             $errors=$this->validate($post_data,['name'=>'required','dob'=>'required','gender'=>'required','skills'=>'required','image'=>'required','description'=>'required']);

            if($errors)
                return back();

            else{ 
                $baseUrl="http://localhost/CollegeManagement/public/images/";
                $imageName = time().'.'.$post_data->image->getClientOriginalExtension();//Getting the Image Name.
                $post_data->image->move(public_path('images'), $imageName);//Moving the Image file from /tmp/phpd...to images directory
                $imageUrl=$baseUrl.$imageName;
                $student_details=student_details::find($id);
                $student_details->skills="";
                $student_details->name=$post_data->name;
                $student_details->dob=$post_data->dob;
                $student_details->age=$post_data->age;
                $student_details->gender=$post_data->gender;
                $skills=$post_data->skills;
                foreach ($skills as $skills_details) {
                    $student_details->skills.=$skills_details.",";
                }
                $student_details->image=$imageUrl;
                $student_details->description=$post_data->description;
                $student_details->save();
                return redirect('/admin');
            }
    }

    public function delete_student($id){
            $student_details=student_details::find($id);
            $student_details->delete();
            return back();
    }

}
