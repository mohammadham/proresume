<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use App\Models\User\Language as UserLanguage;

class UpdateController extends Controller
{
    public function version()
    {
        return view('updater.version');
    }

    public function recurse_copy($src, $dst)
    {
        // dd(base_path($src), base_path($dst));
        $dir = opendir(base_path($src));
        @mkdir(base_path($dst), 0775, true);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir(base_path($src) . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy(base_path($src . '/' . $file), base_path($dst) . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function upversion(Request $request)
    {

        $assets = array(
            ['path' => 'app', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'config', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'database/migrations', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'resources/views', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'routes', 'type' => 'folder', 'action' => 'replace'],

            ['path' => 'assets/admin/css', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'assets/admin/js', 'type' => 'folder', 'action' => 'replace'],

            ['path' => 'assets/front/css', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'assets/front/js', 'type' => 'folder', 'action' => 'replace'],
            ['path' => 'assets/user/js', 'type' => 'folder', 'action' => 'replace'],

            ['path' => 'assets/front/theme9', 'type' => 'folder', 'action' => 'add'],
            ['path' => 'assets/front/theme9-12', 'type' => 'folder', 'action' => 'add'],
            ['path' => 'assets/front/theme10', 'type' => 'folder', 'action' => 'add'],
            ['path' => 'assets/front/theme11', 'type' => 'folder', 'action' => 'add'],
            ['path' => 'assets/front/theme12', 'type' => 'folder', 'action' => 'add'],


            ['path' => 'composer.json', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'composer.lock', 'type' => 'file', 'action' => 'replace'],
            ['path' => 'version.json', 'type' => 'file', 'action' => 'replace']


        );
        foreach ($assets as $key => $asset) {
            $des = '';
            if (strpos($asset["path"], 'assets/') !== false) {
                $des = 'public/' . $asset["path"];
            } else {
                $des = $asset["path"];
            }
            // if updater need to replace files / folder (with/without content)
            if ($asset['action'] == 'replace') {
                if ($asset['type'] == 'file') {
                    copy(base_path('public/updater/' . $asset["path"]), base_path($des));
                }
                if ($asset['type'] == 'folder') {
                    $this->delete_directory(base_path($des));
                    $this->recurse_copy('public/updater/' . $asset["path"], $des);
                }
            }
            // if updater need to add files / folder (with/without content)
            elseif ($asset['action'] == 'add') {

                if ($asset['type'] == 'folder') {

                    $this->recurse_copy('public/updater/' . $asset["path"], $des);
                }
                if ($asset['type'] == 'file') {
                    $source = base_path('public/updater/' . $asset["path"]);
                    $destination = base_path($des);

                    $dir = dirname($destination);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    if (file_exists($source)) {
                        copy($source, $destination);
                    }
                }
            }
        }


        $this->updateLanguage();
        Artisan::call('config:clear');
        Artisan::call('migrate');
        Session::flash('success', 'Updated successfully');
        return redirect('updater/success.php');
    }

    function delete_directory($dirname)
    {
        $dir_handle = null;
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while ($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname . "/" . $file))
                    unlink($dirname . "/" . $file);
                else
                    $this->delete_directory($dirname . '/' . $file);
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }

    public function redirectToWebsite(Request $request)
    {
        $arr = ['WEBSITE_HOST' => $request->website_host];
        setEnvironmentValue($arr);
        \Artisan::call('config:clear');

        return redirect()->route('front.index');
    }

    public function updateLanguage()
    {
        $keywordGroups = [
            'user' => [
                "Work Process" => "Work Process",
                "Theme 9" => "Theme 9",
                "Theme 10" => "Theme 10",
                "Footer Section" => "Footer Section",
                "Background Image" => "Background Image",
                "Hero Section Background Image " => "Hero Section Background Image ",
                "Work Process Section" => "Work Process Section",
                "Hero Section Background Image" => "Hero Section Background Image",
                "Work Process Section Title" => "Work Process Section Title",
                "Button Name" => "Button Name",
                "Button Url" => "Button Url",
                "Call to Action Section" => "Call to Action Section",
                "Call to Action Section Background Image" => "Call to Action Section Background Image",
                "Call to Action Section Image" => "Call to Action Section Image",
                "Call to Action Section Title" => "Call to Action Section Title",
                "Call to Action Section Button Name" => "Call to Action Section Button Name",
                "Call to Action Section button url" => "Call to Action Section button url",
                "wrap the text with a" => "wrap the text with a",
                "tag to style it like" => "tag to style it like",
                "this screenshot" => "this screenshot",
                "Rating Text" => "Rating Text",
                "Experience Text" => "Experience Text",
                "Hero Section Subtitle" => "Hero Section Subtitle",
                "Hero Section Title" => "Hero Section Title",
                "Appointment Section" => "Appointment Section",
                "Appointment Section Title" => "Appointment Section Title",
                "Appointment Section Subtitle" => "Appointment Section Subtitle",
                "Hero Section Video Image" => "Hero Section Video Image",
                "Hero Section Video Subtitle" => "Hero Section Video Subtitle",
                "Hero Section Video Title" => "Hero Section Video Title",
                "Hero Section Video Url" => "Hero Section Video URL",
                "About Section Left Image" => "About Section Left Image",
                "About Section Right Image" => "About Section Right Image",
                "About Section Middle Image" => "About Section Middle Image",
                "Gallery Section" => "Gallery Section",
                "Gallery Section Title" => "Gallery Section Title",
                "Video Url" => "Video URL",
                "Video Button Text" => "Video Button Text",
                "Features Section" => "Features Section",
                "Features Section Title" => "Features Section Title",
                "Features Section Subtitle" => "Features Section Subtitle",
                "Features Section Image" => "Features Section Image",
                "Features Image Title" => "Features Image Title",
                "Add New" => "Add New",
                "Edit Process" => "Edit Process",
                "Add Process" => "Add Process",
                "The higher the serial number is, the later the work process will be shown." => "The higher the serial number is, the later the work process will be shown.",
                "Enter subtitle" => "Enter subtitle",
                "Symbol" => "Symbol",
                "Enter symbol" => "Enter symbol",
                "Theme 11" => "Theme 11",
                "Theme 12" => "Theme 12",
                "Posts" => "Posts",
                "Blog" => "Blog"
            ],
            'admin' => [
                "Preview" => "Preview",
                "vCard Name" => "vCard Name",
                "vCards" => "vCards",
                "Users vCards" => "User vCards",
                "vCard URLs" => "vCard URLs",
                "Subdomain Based URL" => "Subdomain Based URL",
                "Domain Based URL" => "Domain Based URL",
                "Edit Template" => "Edit Template",
                "Gallery" => "Gallery",
                "Theme 9" => "Theme 9",
                "Theme 10" => "Theme 10",
                "Theme 11" => "Theme 11",
                "Theme 12" => "Theme 12"
            ],
            'user_front' => [
                "Read_All_Post" => "Read All Post",
                "Stay_Connected_With_Me" => "Stay Connected With Me",
                "Book_Now" => "Book Now",
                "Make_Appointment" => "Make Appointment",
                "Time" => "Time",
                "Day" => "Day",
                "Open" => "Open",
                "Closed" => "Closed",
                "Business_Day_Of_Appointment" => "Business Day Of Appointment",
                "star_of" => "star of",
                "review" => "review",
                "Gallery" => "Gallery",
                "Blog" => "Blog",
                "Account" => "Account",
            ],
        ];
        $this->userfrontKeyword($keywordGroups['user_front']);
        $this->adminKeyword($keywordGroups['admin']);
        $this->userDashboardKeyword($keywordGroups['user']);
    }

    public function userfrontKeyword($new_keywords)
    {
        $user_languages = UserLanguage::all();
        foreach ($user_languages as $user_language) {
            $langKeywods = json_decode($user_language->keywords, true);
            $mergeKeywords = array_merge($langKeywods, $new_keywords);

            $user_language->keywords = json_encode($mergeKeywords);
            $user_language->save();
        }

        $admin_languages = Language::all();
        foreach ($admin_languages as $admin_language) {
            $langKeywods = json_decode($admin_language->customer_keywords, true);
            $mergeKeywords = array_merge($langKeywods, $new_keywords);

            $admin_language->customer_keywords = json_encode($mergeKeywords);
            $admin_language->save();
        }
    }
    public function userDashboardKeyword($new_keywords)
    {
        // update default JSON
        $jsonPath = resource_path('lang/user_default.json');
        $jsonData = file_get_contents($jsonPath);
        $keywords = json_decode($jsonData, true);

        // merge old + new keywords
        $datas = array_merge($keywords, $new_keywords);

        // save updated file
        $newJson = json_encode($datas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($jsonPath, $newJson);

        // update all languages
        $languages = UserLanguage::all();
        foreach ($languages as $language) {

            $langFile = resource_path('lang/user_' . $language->code . '.json');
            if (!file_exists($langFile)) {
                file_put_contents($langFile, "{}");
            }

            $LanguagejsonData = file_get_contents($langFile);
            $Languagekeywords = json_decode($LanguagejsonData, true) ?? [];

            // merge old + new keywords
            $langWiseDatas = array_merge($Languagekeywords, $new_keywords);

            // Save updated file per language
            $langJson = json_encode($langWiseDatas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($langFile, $langJson);
        }
    }
    public function adminKeyword($new_keywords)
    {
        // update default JSON
        $jsonPath = resource_path('lang/admin_default.json');
        $jsonData = file_get_contents($jsonPath);
        $keywords = json_decode($jsonData, true);

        // merge old + new keywords
        $datas = array_merge($keywords, $new_keywords);

        // save updated file
        $newJson = json_encode($datas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($jsonPath, $newJson);

        // update all languages
        $languages = Language::all();
        foreach ($languages as $language) {

            $langFile = resource_path('lang/admin_' . $language->code . '.json');
            if (!file_exists($langFile)) {
                file_put_contents($langFile, "{}");
            }

            $LanguagejsonData = file_get_contents($langFile);
            $Languagekeywords = json_decode($LanguagejsonData, true) ?? [];

            // merge old + new keywords
            $langWiseDatas = array_merge($Languagekeywords, $new_keywords);

            // Save updated file per language
            $langJson = json_encode($langWiseDatas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            file_put_contents($langFile, $langJson);
        }
    }
}
