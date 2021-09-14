<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use Illuminate\Http\Request;
Use App\NewsGallery;
use App\News;
use App\OldNews;
Use App\AcademicAndNonAcademic;
Use App\Didtable;
Use App\Deptt;
Use App\Tarea;
Use App\Contacts;
Use App\NewsPub;
Use App\Attendance;
Use App\Dudownloads;
Use App\Loa;
Use App\Si;
Use App\Gfrp;
Use App\Il;
Use App\NewNews;
Use App\Texteditor;
Use App\Upanel;
Use App\Department;
Use App\SubType;
Use App\RelatedLink;
Use App\RelatedLinkData;
Use App\ResearchScholars;
Use App\UserContact;
Use App\UserVisibility;
use App\NoticeCircular;
Use App\StudyMatrial;
Use App\Qualification;
Use App\User;
Use App\Journal;
Use App\Journals;
Use App\ThrustArea;
Use App\OldUser;
Use App\Designation;
Use App\NewsPublication;
Use App\Cnm;
Use App\NewTender;
Use App\Tender;
Use App\Events;
Use App\AcNonAcGallery;
Use App\FileManagement;
Use App\MOU;
use DateTime;
use Redirect;
use DirectoryIterator;
use DB;

class DataMigration extends BaseController
{
    public $rid; 
    public $dir = "F:\wamp64\www\dev.amu.com\storage\app\public\images\academic\gallery";
    
    public function newsMigration()
    {
        $locale='en';
        //$oldnews = OldNews::where('id','6455')->get();
        $oldnews = DB::table('newnews')->get();
        $input =array();
       $data = array();
        foreach ($oldnews as $key => $n) {
            $newText =array();
            $str = nl2br(trim($n->story), false);
            $str = explode('<br>', $str);
            foreach ($str as $k => $value) {
                 if($k > 0){
                    $newText[$k] = "<p>".$value."</p>";
                 }
            } 
            $title = $this->substrwords($str[0], 250);
            $slug = $this->substrwords($str[0], 100);
            $detail = implode('', $newText);  

            $slug = str_replace(' ', '-', $slug);
            $slug = str_replace('.', '', $slug);
            $slug = str_replace(',', '', $slug);
            $image = DB::table('newimage')->where('nid',$n->nid)->first();
            if(!empty($image)){
              $data['file'] = '/images/news_gallery/'.$image->name;
            }else{
              $data['file'] = NULL;
            }
            // $data[$key]['nid'] = $n->nid;
            // $data[$key]['title_en'] = trim($title); 
            // $data[$key]['description_en'] = $detail;
            // $data[$key]['slug'] = string_cut(strtolower($slug), 100,'');
            // $data[$key]['date'] = date("Y-m-d", strtotime($n->date));
            //-----------------------------
            $data['nid'] = $n->nid;
            $data['title_en'] = trim($str[0]); 
            $data['description_en'] = $detail;
            $data['slug'] = string_cut(strtolower($slug), 100,'');
            $data['date'] = date("Y-m-d", strtotime($n->date));
            $data['status'] = 1;
            News::create($data);
        }
       return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
        
    }

    public function departmentMigration()
    {
        $locale='en';
        //$oldnews = OldNews::where('id','6455')->get();
        $oldnews = Didtable::orderBy('id','asc')->get();
        $input =array();
        //$data = array();
        foreach ($oldnews as $key => $n) {                
            $slug = str_slug(trim($n->dname), '-');
            $input['did'] = $n->did;
            $input['title_en'] = trim($n->dname); 
            $input['type'] = '1';
            $input['slug'] = $slug;
            $input['sub_type'] = '2';
            $input['status'] = '1';
            //AcademicAndNonAcademic::create($input);
        }
        $msg = 'Department migration done';
        dd($msg);
    }

    public function departmentLinkCreation()
    {
        $locale='en';
        //$oldnews = OldNews::where('id','6455')->get();
        $academic = AcademicAndNonAcademic::orderBy('id','asc')->with('getSubType')->get();
        $input =array();
        foreach ($academic as $key => $n) { 
            $sub_type_slug = str_slug($n->getSubType->title, '-');
            $slug = $sub_type_slug.'/'.$n->slug;
            $dt = new DateTime();
            $id = $n->id ;             
            $input = array(
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '1','link_name_en'=>'ABOUT THE DEPARTMENT','link_name_hi'=>'????? ?? ???? ???','link_name_ur'=>'???????? ?? ???? ????','link_order'=>'1','slug' => $slug.'/about-the-department','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '1','link_name_en'=>'FACULTY MEMBERS','link_name_hi'=>'?????? ??','link_name_ur'=>'?????? ?? ?????','link_order'=>'1','slug' => $slug.'/faculty-members','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '1','link_name_en'=>'NON TEACHING STAFF','link_name_hi'=>'???????? ???????? ????','link_name_ur'=>'??? ????? ??????','link_order'=>'1','slug' => $slug.'/non-teaching-staff','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '1','link_name_en'=>'P. G. STUDENTS','link_name_hi'=>'??????????? ?????','link_name_ur'=>'???? ??????? ??????','link_order'=>'1','slug' => $slug.'/pg-student','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '1','link_name_en'=>'LIST OF FORMER CHAIRPERSON','link_name_hi'=>'??????? ?? ????','link_name_ur'=>'???? ???????? ?? ??????','link_order'=>'1','slug' => $slug.'/list-of-former-chairperson','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '2','link_name_en'=>'Ph.D.','link_name_hi'=>'????.??.','link_name_ur'=>'?? ??? ??','link_order'=>'2','slug' => $slug.'/phd','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '2','link_name_en'=>'Post Graduate','link_name_hi'=>'????? ????????','link_name_ur'=>'???? ????????','link_order'=>'2','slug' => $slug.'/post-graduate','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '2','link_name_en'=>'Under Graduate','link_name_hi'=>'?????? ?? ???','link_name_ur'=>'???????????','link_order'=>'2','slug' => $slug.'/under-graduate','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '3','link_name_en'=>'THRUST AREA','link_name_hi'=>'?? ??????? ??? ???','link_name_ur'=>'????? ??????','link_order'=>'3','slug' => $slug.'/thrust-area','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '3','link_name_en'=>'ON GOING RESEARCH PROJECTS','link_name_hi'=>'???????? ?????????? ?? ?? ??? ???','link_name_ur'=>'????? ???????? ????? ????','link_order'=>'3','slug' => $slug.'/on-going-research-projects','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '3','link_name_en'=>'COMPLETED RESEARCH PROJECTS','link_name_hi'=>'???? ???????? ??????????','link_name_ur'=>'???? ????? ?????????','link_order'=>'3','slug' => $slug.'/completed-research-projects','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '3','link_name_en'=>'JOINT PROJECT','link_name_hi'=>'??????? ????????','link_name_ur'=>'???? ???? ?? ??????','link_order'=>'3','slug' => $slug.'/joint-projects','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '3','link_name_en'=>'IMPORTANT LABORATORIES','link_name_hi'=>'?????????? ????????????','link_name_ur'=>'??? ??????????','link_order'=>'3','slug' => $slug.'/important-laboratories','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '4','link_name_en'=>'ALUMNI RELATIONS','link_name_hi'=>'????? ????? ?????','link_name_ur'=>'???????? ????????','link_order'=>'4','slug' => $slug.'/alumni-relations','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '4','link_name_en'=>'CAREER COUNSELLING COMMITTEE','link_name_hi'=>'?????? ?????? ?????','link_name_ur'=>'?????? ??????? ??????','link_order'=>'4','slug' => $slug.'/career-counselling-committee','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'STUDENT`S ATTENDANCE RECORD','link_name_hi'=>'??????? ?? ???????? ???????','link_name_ur'=>'???? ????? ?? ????? ?? ???????','link_order'=>'5','slug' => $slug.'/student-attendance-record','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'GRANTS AND FUNDING','link_name_hi'=>'?????? ?? ????????','link_name_ur'=>'????? ??? ??????','link_order'=>'5','slug' => $slug.'/grants-and-funding','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'SEMINAR/CONFERENCE/WORKSHOP EVENTS','link_name_hi'=>'??????? / ?????????? / ???????????','link_name_ur'=>'??????? / ??????? / ?????? ???????','link_order'=>'5','slug' => $slug.'/seminar-conference-workshop','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'NEWS AND PUBLICATION','link_name_hi'=>'?????? ?? ???????','link_name_ur'=>'????? ??? ??????','link_order'=>'5','slug' => $slug.'/news-publication','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'NOTICES AND CIRCULAR','link_name_hi'=>'????? ?? ?????','link_name_ur'=>'???? ??? ??????','link_order'=>'5','slug' => $slug.'/notice-and-circular','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'USEFUL DOWNLOADS','link_name_hi'=>'?????? ???????','link_name_ur'=>'???? ???? ???','link_order'=>'5','slug' => $slug.'/useful-download','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'EMINENT SPEAKERS','link_name_hi'=>'?????? ???????','link_name_ur'=>'??????? ???????','link_order'=>'5','slug' => $slug.'/eminent-speakers','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'NOTABLE ALUMNI','link_name_hi'=>'????????? ????? ?????','link_name_ur'=>'???? ??? ???????','link_order'=>'5','slug' => $slug.'/notable-alumni','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'OUTREACH ','link_name_hi'=>'????????? ????? ?????','link_name_ur'=>'??? ????','link_order'=>'5','slug' => $slug.'/outreach','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'PHOTO GALLERY','link_name_hi'=>'????? ?????????','link_name_ur'=>'???? ??????','link_order'=>'5','slug' => $slug.'/photo-gallery','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'RTI RELATED INFORMATION','link_name_hi'=>'?????? ??????? ???????','link_name_ur'=>'?? ?? ??? ?? ????? ????????','link_order'=>'5','slug' => $slug.'/rti-related-information','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'TRAINING AND PLACEMENT','link_name_hi'=>'????????? ?? ?????','link_name_ur'=>'????? ??? ???','link_order'=>'5','slug' => $slug.'/training-and-placement','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'JOURNALS','link_name_hi'=>'?????????','link_name_ur'=>'???','link_order'=>'5','slug' => $slug.'/journals','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'MEDALS AND AWARDS','link_name_hi'=>'??? ?? ????????','link_name_ur'=>'?????? ??? ????????','link_order'=>'5','slug' => $slug.'/medals-and-awards','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'CONTACT US','link_name_hi'=>'???? ?????? ????','link_name_ur'=>'?? ?? ????? ????','link_order'=>'5','slug' => $slug.'/contact-us','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
            array('ac_non_ac_id'=>$id, 'group_type_id'=> '5','link_name_en'=>'RETIRED FACULTY MEMBERS','link_name_hi'=>'?????? ????? ?????','link_name_ur'=>' ?????? ?????? ????','link_order'=>'5','slug' => $slug.'/retired-faculty-members','created_at'=>$dt->format('Y-m-d H:i:s'),'updated_at' => $dt->format('Y-m-d H:i:s')),
        );
            //RelatedLink::insert($input);
        }
        $msg = 'Department links done';
        dd($msg);
    }

    public function departmentLinkData()
    {
        $locale='en';
        $relatedLink = RelatedLink::groupBy('ac_non_ac_id')->with('getAcAndNonAc')->where('link_name_en', 'like', 'NEWS AND PUBLICATION')->get();
        //dd($relatedLink);
        $input =array();
        foreach ($relatedLink as $key => $n) { 
            if(!empty($n->getAcAndNonAc)){
            $input[$key]['type']=$n->getAcAndNonAc->type;
            $tarea = NewsPublication::where('did', $n->ac_non_ac_id)->first();
            if(!empty($tarea)){
                if(!empty($tarea->loam)){
                    $newText =array();
                    $str = nl2br(trim($tarea->loam), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $value) {
                       $newText[$k] = "<p>".$value."</p>";
                    } 
                    $detail = implode('', $newText);
                    $ta = $detail;
                }else{
                    $ta = '';
                }
                if(!empty($tarea->dt)){
                    $dt = $tarea->dt;
                }else{
                    $dt = date('Y-m-d');
                }
                $input['type_id'] = $n->getAcAndNonAc->type;
                $input['ac_non_ac_id'] = $n->ac_non_ac_id;
                $input['related_link_id'] = $n->id;
                $input['link_en'] = 'News and Publication';
                $input['link_description_en'] = $ta;
                $input['created_at'] = $dt;
                $input['updated_at'] = $dt;
                $input['approval_status'] = 'Approved';
                //RelatedLinkData::create($input);
                }
            } 
        }
        $msg = 'NEWS AND PUBLICATION data done';
        dd($msg);
    }


    public function departmentLinkFileData()
    {
        $locale='en';
        $relatedLink = RelatedLink::groupBy('ac_non_ac_id')->with('getAcAndNonAc')->where('link_name_en', 'like', 'JOURNALS')->get();
        $input =array();
        $i =0;
        foreach ($relatedLink as $key => $n) {
            $Journals = Journals::where('did', $n->ac_non_ac_id)->get();
            if(!empty($Journals)){
                $i++;
                foreach ($Journals as $k => $value) {
                    $input['academic_type_id'] = '1';
                    $input['ac_non_ac_id'] = $n->ac_non_ac_id;
                    $input['related_link_id'] = $n->id;
                    $input['link_en'] = 'Journals';
                    $input['link_description_en'] = $value->dsc;
                    $input['file'] = 'File/academic/journals/'.$value->fn;  
                    $input['created_at'] = $value->dt;
                    $input['updated_at'] = $value->dt;
                    //RelatedLinkData::create($input);
                } 
            }           
             
        }
        $msg = 'JOURNALS';
        dd($msg.'='.$i);
    }

    public function departmentAboutUsData()
    {
        $locale='en';
        $data = Deptt::get();
        $input =array();
        foreach ($data as $key => $n) {
            $newText =array();
            $str = nl2br(trim($n->about_old), false);
            $str = explode('<br>', $str);
            foreach ($str as $k => $value) {
               $newText[$k] = "<p>".$value."</p>";
            } 
            $detail = implode('', $newText); 
            $input['about'] = $detail;
            //Deptt::where('id','=',$n->id)->update($input);            
        }
        $msg = 'Department link data done';
        dd($msg);
    }

    public function researchScholarsMigration()
    {
        $locale='en';
        $oldnews = ResearchScholars::get();
        foreach ($oldnews as $key => $n) {
            $input =array();
                
            $slug = slug(trim($n->name));
            $input['slug'] = $slug;
            //ResearchScholars::where('id',$n->id)update($input);
        }
        $msg = 'News migration done';
        dd($msg);
    }

    public function noticeCircularMigration()
    {
        $locale='en';
        $cnm = DB::table('dnotice')->get();
        foreach ($cnm as $key => $n) {
            $input =array();                
            //$slug = str_slug(trim($n->dsc), '-');
            $input['title_en'] = $n->dsc;
            //$input['slug'] = NULL;
            $input['description_en'] = $n->dsc;
            $input['file'] = '/file/notice/'.$n->fn;
            $input['ac_non_ac_id'] = $n->did;
            $input['date'] = date("Y-m-d", strtotime($n->dt));
            $input['status'] = '1';
            $input['approval_status'] = 'Approved';
           // dd($input);
            //NoticeCircular::create($input);
        }
        $msg = 'Notice Circular migration done';
        dd($msg);
    }

    public function tenderMigration()
    {
        $locale='en';
        $cnm = DB::table('newtender')->get();
        foreach ($cnm as $key => $n) {
            // $data[$key]['id'] = $n->nid;
            // $data[$key]['title_en'] = trim($str[0]); 
            // $data[$key]['description_en'] = $detail;
            // $data[$key]['slug'] = string_cut(strtolower($slug), 100,'');
            // $data[$key]['date'] = date("Y-m-d", strtotime($n->date));
            $input =array();            
            $input['description'] = $n->dsc;
            $input['file'] = '/file/tender/'.$n->ftype;
            $input['date'] = date("Y-m-d", strtotime($n->date));
            $input['tender_type'] = $n->tp;
            $input['approval_status'] = 'Approved';
            Tender::create($input);
        }
        return $this->sendResponse($input, trans($locale.'_lang.DATA_FOUND'),200); 
    }


    public function nonAcademicMigration()
    {
        $locale='en';
        $upanels = Upanel::with('getDepartments','getLinks')->get();
        foreach ($upanels as $key => $n) {
            $input =array();
            $slug = '';            
            if(!empty($n->getDepartments) && $n->getDepartments->pname == 'Academies'){
                $sub_type = '13';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Central Facilities'){
                $sub_type = '12';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Centres'){
                $sub_type = '5';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Colleges'){
                $sub_type = '3';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Halls'){
                $sub_type = '9';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Institutes'){
                $sub_type = '7';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Libraries'){
                $sub_type = '8';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Miscellaneous'){
                $sub_type = '10';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Schools'){
                $sub_type = '4';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'Training & Placement'){
                $sub_type = '11';
            }elseif(!empty($n->getDepartments) && $n->getDepartments->pname == 'University Polytechnic'){
                $sub_type = '6';
            }else{
                $sub_type = Null;
            }
            $slug = str_slug($n->uname);
            $input['id'] = $n->uid;
            $input['did'] = $n->uid;
            $input['slug'] = $slug;
            $input['title_en'] = $n->uname;
            $input['type'] = '2';
            $input['sub_type'] = $sub_type;
            AcademicAndNonAcademic::create($input);
        }
        $msg = 'Non Academic migration done';
        return $this->sendResponse($msg, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function nonAcademicLinksAndDataMigration()
    {
        $locale='en';
        //$data = Texteditor::with('getAcademicAndNonAcademic')->where('did','>','10273')->get();
        $data = Texteditor::with('getAcademicAndNonAcademic')->paginate('500');;
        //return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
        $id =array();
        foreach ($data as $key => $n) {
            $input =array();
            $result='';
            if(!empty($n->getAcademicAndNonAcademic)){
                $base = $n->getAcademicAndNonAcademic->slug;           
                $base1 = str_slug($n->fieldname);
                $slug = $base.'/'.$base1;
                $input['ac_non_ac_id'] = $n->did;
                $input['slug'] = $slug;
                $input['link_name_en'] = $n->fieldname;
                $input['group_type_id'] = '0';
                $result = RelatedLink::create($input);
                $id[$key]['RelatedLink']=$n->did;
                $input1 =array();
                $input1['academic_type_id'] = '2';            
                $input1['ac_non_ac_id'] = $n->did;
                $input1['related_link_id'] = $result->id;
                $input1['link_en'] = $n->fieldname;
                $input1['link_description_en'] = $n->story;
                RelatedLinkData::create($input1);
                $id[$key]['RelatedLinkData']=$result->id;  
            }
            
        }
        $msg = 'Non Academic Data migration done';
        return $this->sendResponse($id, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function mouMigration()
    {
        $locale='en';
        //$mou = DB::table('ec_minutes')->where('tp','Executive Council')->get();
        //$mou = DB::table('ec_minutes')->where('tp','Academic Council')->get();
        $mou = DB::table('ec_minutes')->where('tp','University Court')->get();        
        foreach ($mou as $key => $n) {
            $input =array();
            $input['ac_non_ac_id'] = 10071;
            //$input['related_link_id'] = 9717;
            //$input['related_link_id'] = 9718;
            $input['related_link_id'] = 9719;
            $input['link_en'] = $n->dsc;
            $input['file'] = 'file/minutes-of-meeting/'.$n->fn;
            $input['type_id'] = '2';
            $input['approval_status'] = 'Approved';
            $input['updated_at'] = $n->dt;
            //RelatedLinkData::create($input);
        }
        $msg = 'MoU migration done';
        dd($msg);
    }


    public function userDataMigration()
    {
        $locale='en';
        $oldUsers = DB::table('retirefaculty')
                    ->whereNotIn('eid', ['03807','00408','08106','03408','05706','05814','04020','05407','09302','07501','06008','04705','05306','07502','02210','01701','02207','02205','08102','05815','02209','03902','07407','04102','00505','03103','00508','00515','08109','03104','01204','08108','05905','05608','05301','06203','07608','02202','00102'])
                    ->get();
        //dd($oldUsers);
        $data =array();
        foreach ($oldUsers as $key => $n) {
            $user =array();
            $contact_detail=array();
            $journal=array();
            $qualification=array();
            $thrustArea=array();
            $userVisibility=array();
            $desig = '';
            $slug = '';
            $name = '';
            $first_name = '';
            $last_name = '';
            $middle_name ='';
            $ac_non_ac = '';
            $sub_type ='';

            $slug = str_slug($n->name);
            $name = explode(' ', trim($n->name));
            $first_name = $name['0'];
            if(count($name) == 1){
              $last_name = '';
            }else{
              $last_name = end($name);   
            }
            if(count($name) == 3){
                $middle_name = $name['1'];
            }elseif(count($name) == 4){
                $middle_name = $name['1'].' '.$name['2'];
            }elseif(count($name) == 5){
                $middle_name = $name['1'].' '.$name['2'].' '.$name['3'];
            }else{
                $middle_name = '';
            }
            

            //$user['updated_at'] = $n->updated_at;
            $user['eid'] = $n->eid;
            $user['slug'] = $slug;
            $user['title'] = $n->pref;
            $user['first_name'] = ucfirst(strtolower($first_name));
            $user['last_name'] = ucfirst(strtolower($last_name));
            $user['middle_name'] = ucwords(strtolower($middle_name));
            $user['status'] = 1;
            $user['for_id'] = $n->cat;
            $user['profile'] = $n->profile;
            //$user['dob'] = date("Y-m-d", strtotime($n->dob));
            //$user['image'] = '/images/empphoto/'.$n->eid.'.jpg';
            //$user['cv'] = '/images/empcv/'.$n->eid.'.pdf';
            //dd($user);
            //$id = User::create($user);
            //dd($id->id);
            $contact_detail['user_id'] = $id->id;
            $contact_detail['email'] = $n->email;
            $contact_detail['mobile_no'] = $n->mobile;
            $contact_detail['telephone_no'] = $n->telephone; 
            $contact_detail['address'] = $n->pha; 
            //$contact_detail['updated_at'] = $n->updated_at;
            //UserContact::create($contact_detail);
            
            if($n->publication != NULL && $n->publication != ''){
                $journal['user_id'] = $id->id;
                $journal['title'] = "Publication";
                $journal['description'] = $n->publication;
                //Journal::create($journal);
            }
            
            if($n->qualif != NULL && $n->qualif != ''){
                $qualification['user_id'] = $id->id;
                $qualification['qualification'] = $n->qualif;
                //Qualification::create($qualification);
            }

            if($n->thrust != NULL && $n->thrust != ''){
                $thrustArea['user_id'] = $id->id;
                $thrustArea['title'] = $n->thrust;
                //ThrustArea::create($thrustArea);
            }

            $ac_non_ac = AcademicAndNonAcademic::where('id', $n->dname)->first();
            if(!empty($ac_non_ac)){
               $sub_type = $ac_non_ac->sub_type;
            }else{
               $sub_type = NULL; 
            }
            $desig = Designation::where('name', 'like' ,trim($n->desig))->first();
            if(!empty($desig)){
               $designation_id = $desig->id;
            }else{
               $designation_id = NULL; 
            }
            if($n->chair == 'Yes'){
                $role = '2';
            }else{
                $role = '3'; 
            }

            $userVisibility['user_id'] = $id->id;
            $userVisibility['type_id'] = $n->cat;
            $userVisibility['sub_type_id'] = $sub_type;
            $userVisibility['ac_non_ac_id'] = $n->dname;
            $userVisibility['designation_id'] = $designation_id;
            $userVisibility['role_id'] = $role;
            $userVisibility['core'] = '1';
            //$userVisibility['updated_at'] = $n->updated_at;
            $userVisibility['order_on'] = $n->orderlist;
            $userVisibility['status'] = '2';
            //UserVisibility::create($userVisibility); 

        }
        $msg = 'User migration done';
        return $this->sendResponse($msg, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function userImage(){
        $locale='en';
        $userImage = array();
        $userID = User::select('id')->paginate('1000');
        foreach ($userID as $key => $value) {
            if(@getimagesize('https://www.amu.ac.in/empphoto/'.$value->id.'.jpg')){
                $userImage[$key]['image'] = 'ok';
            }else{
                $userImage[$key]['image'] = 'Not OK';
                User::where('id', $value->id)->update(['image' => 'NULL']);
            }
        }
        return $this->sendResponse($userImage, trans($locale.'_lang.DATA_FOUND'),200);
    }


    public function userurl(){
        $locale='en';
        $userUrl = array();
        $userNewUrl = array();
        $user = DB::table('users')->select(DB::raw('count(email) as email, url, slug '))->groupBy('slug')->havingRaw('COUNT(slug) > 1')->get();
        //$user = User::where('created_at','>','2020-09-27 00:00:00')->with('getUserCoreRole')->get();
        dd($user);
        foreach($user as $key => $value){
            $u = User::select('url', 'slug', 'id')->where('slug',$value->slug)->get();
            foreach ($u as $k => $v) {
                if($k != 0){
                    $urlold  =  explode('/',$v->url);
                    $urlold[2]=$v->slug.'-'.$k;
                    $newURL = implode("/",$urlold);
                    //User::where('id', $v->id)->update(['url' => $newURL, 'slug' => $v->slug.'-'.$k]);
                }# code...
            }
        }        
        // foreach ($user as $key => $value) {
        //    if(!empty($value->getUserCoreRole) && $value->getUserCoreRole->core == '1'){
        //      $slug = AcademicAndNonAcademic::select('slug')->where('id',$value->getUserCoreRole->ac_non_ac_id)->first();
        //      if(!empty($slug)){
        //         ///if($value->for_id == '1'){
        //             $url = 'faculty/'.$slug->slug.'/'.$value->slug;
        //         //}else{
        //             //$url = 'non-teaching/'.$slug->slug.'/'.$value->slug;
        //         //}
        //         User::where('id', $value->id)->update(['url' => $url]);                
        //      }else{
        //         $url = NULL;
        //         User::where('id', $value->id)->update(['url' => NULL]);
        //      }
        //      $userUrl[$key]['URL'] = $url;
             
        //    }
        // }
        return $this->sendResponse($userUrl, trans($locale.'_lang.DATA_FOUND'),200);
    }


    public function galleryImage($dir = '/home/alig/storage/app/public/images/academic/gallery'){
         $locale='en';
         $data = array();
         $dh = new DirectoryIterator($dir);
         foreach ($dh as $item) {
             if (!$item->isDot()) {
                if ($item->isDir()) {
                    $this->galleryImage("$dir/$item");
                } else {
                    $data = array();
                    $url = $dir . "/" . $item->getFilename();
                    $file = explode('/home/alig/storage/app/public',$url); 
                    $a = explode('/images/academic/gallery',$file['1']);
                    $b = explode('/',$a['1']);
                    $c = explode('.',end($b));
                    array_pop($c);
                    $d =implode(".",$c);
                    $data['ac_non_ac_id'] = $b['1'];
                    $data['image'] = $file['1'];
                    $data['title_en'] = $d;
		    //dd($data);
                    //AcNonAcGallery::create($data);
                }
             }
          }
        return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);  
    }

	public function userStudyMaterial(){
       
	        $locale = 'en';
	        $data = array();
	        $userStudyMaterial = DB::table('videom')->get();
	        foreach($userStudyMaterial as $key => $value){
	                $data['user_id'] = $value->did;
	                $data['title'] = $value->desc;
	                $data['type'] = 'video';
	                $data['video'] = $value->fn;
	                $data['date'] = date("Y-m-d", strtotime($value->dt));
	                //StudyMatrial::create($data);
	        }        
        
	        return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
	}

    public function userStudyMaterialFlie(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('studym')->get();
            foreach($userStudyMaterial as $key => $value){
                    $data['user_id'] = $value->did;
                    $data['title'] = $value->dsc;
                    $data['type'] = 'file';
                    $data['file'] = '/file/study_material/'.$value->fn;
                    $data['date'] = date("Y-m-d", strtotime($value->dt));
                    StudyMatrial::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }


    public function eventMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('addeventhead1')->get();
            foreach($userStudyMaterial as $key => $value){
                    $newText =array();
                    $str = nl2br(trim($value->dsc), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $v) {
                         if($k > 0){
                            $newText[$k] = "<p>".$v."</p>";
                         }
                    } 
                    $detail = implode('', $newText);    
                    $slug = str_replace(' ', '-', trim($str[0]));
                    $slug = str_replace('.', '', $slug);
                    $slug = str_replace(',', '', $slug);
                    
                    // $data[$key]['id'] = $value->id;
                    // $data[$key]['title_en'] = trim($str[0]);
                    // $data[$key]['description_en'] =  $detail; 
                    // $data[$key]['slug'] = $slug;
                    // $data[$key]['event_type'] = $value->tp;
                    // if($value->file){
                    //    $data[$key]['file'] = '/file/events/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }                   
                    // $data[$key]['start_date'] = date("Y-m-d", strtotime($value->dt));
                    // $data[$key]['end_date'] = date("Y-m-d", strtotime($value->dt));
                    
                    $data['title_en'] = trim($str[0]);
                    $data['description_en'] =  $detail; 
                    $data['slug'] = $slug;
                    //$data['event_type'] = $value->tp;
                    if($value->file){
                       $data['file'] = $value->file; 
                    } else{
                        $data['file'] = NULL; 
                    }                   
                    $data['start_date'] = date("Y-m-d", strtotime($value->date));
                    $data['end_date'] = date("Y-m-d", strtotime($value->date));
                    Events::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function journalsMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('journals')->orderBy('did')->where('did', '<>', '143')->get();
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('slug', 'like', '%journals%')->first();
                    $this->rid = $relatedID->id;
                    //dd($this->rid);
                    // $newText =array();
                    // $str = nl2br(trim($value->dsc), false);
                    // $str = explode('<br>', $str);
                    // foreach ($str as $k => $v) {
                    //      if($k > 0){
                    //         $newText[$k] = "<p>".$v."</p>";
                    //      }
                    // } 
                    // $detail = implode('', $newText);    
                    // $slug = str_replace(' ', '-', trim($str[0]));
                    // $slug = str_replace('.', '', $slug);
                    // $slug = str_replace(',', '', $slug);
                    
                    // $data[$key]['id'] = $value->id;
                    // $data[$key]['title_en'] = trim($str[0]);
                    // $data[$key]['description_en'] =  $detail; 
                    // $data[$key]['slug'] = $slug;
                    // $data[$key]['event_type'] = $value->tp;
                    // if($value->file){
                    //    $data[$key]['file'] = '/file/events/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }                   
                    // $data[$key]['start_date'] = date("Y-m-d", strtotime($value->dt));
                    // $data[$key]['end_date'] = date("Y-m-d", strtotime($value->dt));
                    
                    // $data[$key]['related_link_id'] = $this->rid;
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['type'] =  '1'; 
                    // $data[$key]['link_en'] = $value->dsc;
                    // $data[$key]['approval_status'] = 'Approved';
                    // if($value->fn){
                    //    $data[$key]['file'] = '/file/journal/'.$value->fn; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // } 

                    $data['related_link_id'] = $relatedID->id;
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['type'] =  '1'; 
                    $data['link_en'] = $value->dsc;
                    //$data['event_type'] = $value->tp;
                    if($value->fn){
                       $data['file'] = '/file/journal/'.$value->fn; 
                    } else{
                        $data['file'] = NULL; 
                    }
                    $data['approval_status'] = 'Approved'; 
                    $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                    //RelatedLinkData::create($data);
                    //Events::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function seminarMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('events1')->orderBy('did')->paginate(570);
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('link_name_en', 'like', '%seminar/conference/workshop events%')->first();
                    $this->rid = $relatedID->id;
                    //dd($this->rid);
                    $newText =array();
                    $str = nl2br(trim($value->story), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $v) {
                         if($k > 0){
                            $newText[$k] = "<p>".$v."</p>";
                         }
                    } 
                    // $detail = implode('', $newText);    
                    // $slug = str_replace(' ', '-', trim($str[0]));
                    // $slug = str_replace('.', '', $slug);
                    // $slug = str_replace(',', '', $slug);
                    
                    // $data[$key]['id'] = $value->id;
                    // $data[$key]['title_en'] = trim($str[0]);
                    // $data[$key]['description_en'] =  $detail; 
                    // $data[$key]['slug'] = $slug;
                    // $data[$key]['event_type'] = $value->tp;
                    // if($value->file){
                    //    $data[$key]['file'] = '/file/events/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }                   
                    // $data[$key]['start_date'] = date("Y-m-d", strtotime($value->dt));
                    // $data[$key]['end_date'] = date("Y-m-d", strtotime($value->dt));
                    
                    // $data[$key]['related_link_id'] = $this->rid;
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['link_en'] = $this->substrwords(trim($str[0]),'200');
                    // $data[$key]['link_description_en'] = $value->story;
                    // $data[$key]['approval_status'] = 'Approved';
                    // if($value->file !=''){
                    //    $data[$key]['file'] = '/file/seminar/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }
                    // $data[$key]['updated_at'] = date("Y-m-d", strtotime($value->dt));                     

                    $data['related_link_id'] = $relatedID->id;
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['link_en'] = $this->substrwords(trim($str[0]),'250');
                    $data['link_description_en'] = $value->story;
                    if($value->file){
                       $data['file'] = '/file/seminar/'.$value->file; 
                    } else{
                        $data['file'] = NULL; 
                    }
                    $data['approval_status'] = 'Approved'; 
                    $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                    //RelatedLinkData::create($data);
                    //Events::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function trainingMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('tpo1')->orderBy('did')->paginate(110);
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('link_name_en', 'like', '%training and placement%')->first();
                    $this->rid = $relatedID->id;
                    //dd($this->rid);
                    $newText =array();
                    $str = nl2br(trim($value->story), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $v) {
                         if($k > 0){
                            $newText[$k] = "<p>".$v."</p>";
                         }
                    } 
                    // $detail = implode('', $newText);    
                    // $slug = str_replace(' ', '-', trim($str[0]));
                    // $slug = str_replace('.', '', $slug);
                    // $slug = str_replace(',', '', $slug);
                    
                    // $data[$key]['id'] = $value->id;
                    // $data[$key]['title_en'] = trim($str[0]);
                    // $data[$key]['description_en'] =  $detail; 
                    // $data[$key]['slug'] = $slug;
                    // $data[$key]['event_type'] = $value->tp;
                    // if($value->file){
                    //    $data[$key]['file'] = '/file/events/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }                   
                    // $data[$key]['start_date'] = date("Y-m-d", strtotime($value->dt));
                    // $data[$key]['end_date'] = date("Y-m-d", strtotime($value->dt));
                    
                    // $data[$key]['related_link_id'] = $this->rid;
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['link_en'] = $this->substrwords(trim($str[0]),'250');
                    // $data[$key]['link_description_en'] = $value->story;
                    // $data[$key]['approval_status'] = 'Approved';
                    // if($value->file !=''){
                    //    $data[$key]['file'] = '/file/training/'.$value->file; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }
                    // $data[$key]['updated_at'] = date("Y-m-d", strtotime($value->dt));  rtid                   

                    $data['related_link_id'] = $relatedID->id;
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['link_en'] = $this->substrwords(trim($str[0]),'250');
                    $data['link_description_en'] = $value->story;
                    if($value->file){
                       $data['file'] = '/file/training/'.$value->file; 
                    } else{
                        $data['file'] = NULL; 
                    }
                    $data['approval_status'] = 'Approved'; 
                    $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                    RelatedLinkData::create($data);
                    //Events::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function rtiMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('rti')->orderBy('did')->paginate(172);
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('link_name_en', 'like', '%rti related information%')->first();
                    $this->rid = $relatedID->id;
                    //dd($this->rid);
                    $newText =array();
                    $str = nl2br(trim($value->dsc), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $v) {
                         if($k > 0){
                            $newText[$k] = "<p>".$v."</p>";
                         }
                    } 
                    // $data[$key]['related_link_id'] = $relatedID->id;
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['link_en'] = $this->substrwords(trim($str[0]),'250');
                    // $data[$key]['link_description_en'] = $value->dsc;
                    // if($value->fn){
                    //    $data[$key]['file'] = '/file/rtid/'.$value->fn; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }
                    // $data[$key]['approval_status'] = 'Approved'; 
                    // $data[$key]['updated_at'] = date("Y-m-d", strtotime($value->dt));

                    //============================================================                   

                    $data['related_link_id'] = $relatedID->id;
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['link_en'] = $this->substrwords(trim($str[0]),'250');
                    $data['link_description_en'] = $value->dsc;
                    if($value->fn){
                       $data['file'] = '/file/rtid/'.$value->fn; 
                    } else{
                        $data['file'] = NULL; 
                    }
                    $data['approval_status'] = 'Approved'; 
                    $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                   // RelatedLinkData::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function medalsMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('medals')->orderBy('did')->paginate(172);
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('link_name_en', 'like', '%medals and awards%')->first();
                    $this->rid = $relatedID->id;
                    //dd($this->rid);
                    $newText =array();
                    $str = nl2br(trim($value->dsc), false);
                    $str = explode('<br>', $str);
                    foreach ($str as $k => $v) {
                         if($k > 0){
                            $newText[$k] = "<p>".$v."</p>";
                         }
                    } 
                    // $data[$key]['related_link_id'] = $relatedID->id;
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['link_en'] = $this->substrwords(trim($str[0]),'250');
                    // $data[$key]['link_description_en'] = $value->dsc;
                    // if($value->fn){
                    //    $data[$key]['file'] = '/file/medals/'.$value->fn; 
                    // } else{
                    //     $data[$key]['file'] = NULL; 
                    // }
                    // $data[$key]['approval_status'] = 'Approved'; 
                    // $data[$key]['updated_at'] = date("Y-m-d", strtotime($value->dt));

                    //============================================================                   

                    $data['related_link_id'] = $relatedID->id;
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['link_en'] = $this->substrwords(trim($str[0]),'250');
                    $data['link_description_en'] = $value->dsc;
                    if($value->fn){
                       $data['file'] = '/file/medals/'.$value->fn; 
                    } else{
                        $data['file'] = NULL; 
                    }
                    $data['approval_status'] = 'Approved'; 
                    $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                    RelatedLinkData::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function downloadsMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('dudownloads')->get();
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $relatedID = RelatedLink::select('id')->where('ac_non_ac_id',$value->did)->where('link_name_en', 'like', '%useful downloads%')->first();
                    //dd($relatedID->id);
                    if(!empty($relatedID->id)){
                        $newText =array();
                        $str = nl2br(trim($value->dsc), false);
                        $str = explode('<br>', $str);
                        foreach ($str as $k => $v) {
                             if($k > 0){
                                $newText[$k] = "<p>".$v."</p>";
                             }
                        } 
                        // $data[$key]['related_link_id'] = $relatedID->id;
                        // $data[$key]['ac_non_ac_id'] =  $value->did; 
                        // $data[$key]['link_en'] = 'Useful downloads';
                        // $data[$key]['link_description_en'] = $value->dsc;
                        // if($value->fn){
                        //    $data[$key]['file'] = '/file/udownloads/'.$value->fn; 
                        // } else{
                        //     $data[$key]['file'] = NULL; 
                        // }
                        // $data[$key]['approval_status'] = 'Approved'; 
                        // $data[$key]['updated_at'] = date("Y-m-d", strtotime($value->dt));

                        //============================================================      


                        $data['related_link_id'] = $relatedID->id;
                        $data['ac_non_ac_id'] =  $value->did; 
                        $data['link_en'] = 'Useful downloads';
                        $data['link_description_en'] = $value->dsc;
                        if($value->fn){
                           $data['file'] = '/file/udownloads/'.$value->fn; 
                        } else{
                            $data['file'] = NULL; 
                        }
                        $data['approval_status'] = 'Approved'; 
                        $data['updated_at'] = date("Y-m-d", strtotime($value->dt));
                        RelatedLinkData::create($data); 
                    }
                    
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    public function filesMigration(){
       
            $locale = 'en';
            $data = array();
            $userStudyMaterial = DB::table('files_path')->orderBy('did')->get();
            //dd($userStudyMaterial);
            foreach($userStudyMaterial as $key => $value){
                    
                    $str = explode('/', $value->path);
                    
                    // $data[$key]['title'] = $str['1'];
                    // $data[$key]['ac_non_ac_id'] =  $value->did; 
                    // $data[$key]['file'] = $value->path;
                    // $data[$key]['approval_status'] = 'Approved';

                    $data['title'] = $str['1'];
                    $data['ac_non_ac_id'] =  $value->did; 
                    $data['file'] = '/files/file_management/'.$value->path;
                    $data['approval_status'] = 'Approved';
                    FileManagement::create($data);
            }        
        
            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
    }

    function substrwords($text, $limit) {
        $delimiters = [',',' '];
        $marks = ['!','?','.'];

        $phrase = substr($text, 0, $limit);
        $nextSymbol = substr($text, $limit, 1);


        // Equal to original
        if ($phrase == $text) {
        return $phrase;
        }
        // If ends with delimiter
        if (in_array($nextSymbol, $delimiters)) {
        return $phrase;
        }
        // If ends with mark
        if (in_array($nextSymbol, $marks)) {
        return $phrase.$nextSymbol;
        }

        $parts = explode(' ', $phrase);
        array_pop($parts);

        return implode(' ', $parts); // Additioanally you may add ' ...' here.
    }

    public function textEditorHome(){
        $locale = 'en';
        $data = array();
        $dataI = array();
        $textEditorHome = DB::table('TextEditor')->where('fieldname','like', '%Contact Us%')->get();
        foreach ($textEditorHome as $key => $value) {
            $relatedLink =  RelatedLink::where('ac_non_ac_id',$value->did)->where('link_name_en','like', '%Contact Us%')->first();
            if(!empty($relatedLink)){
               $rLD = RelatedLinkData::where('related_link_id',$relatedLink->id)->first();
               if(!empty($rLD)){
                   $relatedLinkData =  '1';//RelatedLinkData::where('related_link_id',$relatedLink->id)->update(['link_description_en' => $value->story]);
                   if($relatedLinkData == 1){
                      $data[$key]['related_link_id'] = $relatedLink->id;
                      $data[$key]['updated'] = 'Yes';
                   }
               }else{
                    $dataI['related_link_id'] = $relatedLink->id;
                    $dataI['ac_non_ac_id'] =  $value->did; 
                    $dataI['link_en'] = 'Contact Us';
                    $dataI['link_description_en'] = $value->story;
                    $dataI['approval_status'] = 'Approved'; 
                    $dataI['updated_at'] = date("Y-m-d", strtotime($value->dt));
                    //RelatedLinkData::create($dataI);
                    $data[$key]['related_link_id'] = $relatedLink->id;
                    $data[$key]['updated'] = 'New';
               }
            }
        }
        return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);    
    }


    public function newsDate(){
        $locale = 'en';
        $data = array();
        $newnews = DB::table('newnews')->paginate('500');
        foreach ($newnews as $key => $value) {
            $newdate = date("Y-m-d", strtotime($value->date));
            DB::table('newnews')->where('nid',$value->nid)->update(['new_date'=> $newdate]);
        }
        return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);    
    }
    
    public function photosOld(){
        $locale = 'en';
        $data = array();
        $photos = DB::table('photogallery')->get();
        foreach ($photos as $key => $value) {
            DB::table('photogallery')->where('id',$value->id)->update(['image'=> '/images/old_gallery/'.$value->ac_non_ac_id.'/'.$value->image]);
        }
        return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);    
    } 

    
}