<?php
if (!defined('_CAN_LOAD_FILES_'))
	exit;
include_once _PS_MODULE_DIR_.'xipblog/config/define.inc.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xipimagetypeclass.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xipcategorypostclass.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xipcommentclass.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xipcategoryclass.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xippostsclass.php';
include_once _PS_MODULE_DIR_.'xipblog/classes/xippostmetaclass.php';
include_once _PS_MODULE_DIR_.'xipblog/controllers/front/main.php';
class xipblog extends Module
{
	public static $xipblogshortname = 'xipblog';
	public static $quick_key = 'xipblogquickaceslink';
	public static $xiplinkobj;
	public static $dispatcherobj;
	public $all_hooks = array("displayheader","ModuleRoutes","displayxipblogleft","displayxipblogright");
	public $fields_arr_path = '/data/fields_array.php';
	public static $css_files = array(
		"xipblog.css",
	);
	public static $js_files = array(
		"xipblog.js",
		"validator.min.js",
	);
	public $all_tabs = array(
		array(
	        'class_name' => 'Adminxippost',
	        'id_parent' => 'parent',
	        'name' => 'Blog Posts',
		),
		array(
	        'class_name' => 'Adminxipcategory',
	        'id_parent' => 'parent',
	        'name' => 'Blog Categories',
		),
		array(
	        'class_name' => 'Adminxipcomment',
	        'id_parent' => 'parent',
	        'name' => 'Blog Comments',
		),
		array(
	        'class_name' => 'Adminxipimagetype',
	        'id_parent' => 'parent',
	        'name' => 'Blog Image Type',
		),
	);
	public $dbfiles = '/db/dbfiles.php';
	public static $ModuleName = 'xipblog';
	public function __construct()
	{
		$this->name = 'xipblog';
		$this->tab = 'front_office_features';
		$this->version = '1.0.0';
		$this->author = 'xpert-idea';
		$this->bootstrap = true;
		$this->controllers = array('archive','single');
		parent::__construct();	
		$this->displayName = $this->l('XipBlog');
		$this->description = $this->l('Prestashop Powerfull Blog Module');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}
	public function install()
	{
		if(!parent::install()
		 || !$this->Register_Hooks()
		 || !$this->Register_Tabs()
		 || !$this->Register_SQL()
		 || !$this->AddQuickAccessLink()
		 || !$this->DummyData()
		 || !$this->InstallSampleData()
		)
			return false;
		return true;
	}
	public function uninstall()
	{
		if(!parent::uninstall()
		 || !$this->UnRegister_Hooks()
		 || !$this->UnRegister_Tabs()
		 || !$this->UnRegister_SQL()
		 || !$this->UnInstallSampleData()
		 || !$this->DeleteQuickAccessLink()
		)
			return false;
		return true;
	}
	public function AddQuickAccessLink(){
	    $link = new Link();
	    $QuickAccess = new QuickAccess();
	    $QuickAccess->link = $link->getAdminLink('AdminModules').'&configure='.$this->name;
	    $languages = Language::getLanguages(false);
	    if(isset($languages) && !empty($languages))
	        foreach($languages as $language)
	            $QuickAccess->name[$language['id_lang']] = $this->l("XipBlog Settings");
	    $QuickAccess->new_window = '0';
	    if($QuickAccess->save())
	        Configuration::updateValue(self::$quick_key,$QuickAccess->id);
	    return true;
	}
	public function DeleteQuickAccessLink(){
        $quick_key = (int)Configuration::get(self::$quick_key);
        if($quick_key != 0){
	        $QuickAccess = new QuickAccess($quick_key);
	        if($QuickAccess->delete()){
	        	return true;	
	        }
        }else{
        	return false;
        }
    }
	public function Register_Hooks()
	{	
		if(isset($this->all_hooks)){
			foreach ($this->all_hooks as $hook) {
        		$this->registerHook($hook);
			}
		}
        return true;
	}
	public function UnRegister_Hooks()
	{
		if(isset($this->all_hooks)){
			foreach ($this->all_hooks as $hook) {
        		$hook_id = Module::getModuleIdByName($hook);
    		    if(isset($hook_id) && !empty($hook_id)){
    		    	$this->unregisterHook((int)$hook_id);
    		    }
			}
		}
        return true;
	}
	public function Register_SQL()
	{
		$querys = array();
		if(file_exists(dirname(__FILE__).$this->dbfiles)){
			require_once(dirname(__FILE__).$this->dbfiles);
			if(isset($querys) && !empty($querys))
				foreach($querys as $query){
					if(!Db::getInstance()->Execute($query))
					    return false;
				}
		}
        return true;
	}
	public function UnRegister_SQL()
	{
		$querys_u = array();
		if(file_exists(dirname(__FILE__).$this->dbfiles)){
			require_once(dirname(__FILE__).$this->dbfiles);
			if(isset($querys_u) && !empty($querys_u))
				foreach($querys_u as $query_u){
					if(!Db::getInstance()->Execute($query_u))
					    return false;
				}
		}
        return true;
	}
	public function UnRegister_Tabs()
	{
		$tabs_lists = array();
			if(isset($this->all_tabs) && !empty($this->all_tabs))
	        foreach($this->all_tabs as $tab_list){
	        	$tab_list_id = Tab::getIdFromClassName($tab_list['class_name']);
	            if(isset($tab_list_id) && !empty($tab_list_id)){
	                $tabobj = new Tab($tab_list_id);
	                $tabobj->delete();
	            }
	        }
        return true;
	}
	public function RegisterParentTabs(){
    	$langs = Language::getLanguages();
    	$save_tab_id = (int)Tab::getIdFromClassName("Adminxipblogsetting");
    	if($save_tab_id != 0){
    		return $save_tab_id;
    	}else{
    		$tab_listobj = new Tab();
    		$tab_listobj->class_name = 'Adminxipblogsetting';
    		$tab_listobj->id_parent = 0;
    		$tab_listobj->module = $this->name;
    		foreach($langs as $l)
    		{
    		    $tab_listobj->name[$l['id_lang']] = $this->l("XipBlog");
    		}
    		if($tab_listobj->save())
    			return (int)$tab_listobj->id;
    		else
    			return (int)$save_tab_id;
    	}
    }
	public function hookModuleRoutes($params)
    {
    	$mainslug = Configuration::get(self::$xipblogshortname."main_blog_url");
    	$postfixslug = Configuration::get(self::$xipblogshortname."postfix_url_format");
    	$categoryslug = Configuration::get(self::$xipblogshortname."category_blog_url");
    	$tagslug = Configuration::get(self::$xipblogshortname."tag_blog_url");
    	$singleslug = Configuration::get(self::$xipblogshortname."single_blog_url");
    	$main_slug = (isset($mainslug) && !empty($mainslug)) ? $mainslug : "xipblog";
    	$postfix_slug = (isset($postfixslug) && !empty($postfixslug) && ($postfixslug == "enable_html")) ? ".html" : "";
    	$category_slug = (isset($categoryslug) && !empty($categoryslug)) ? $categoryslug : "category";
    	$tag_slug = (isset($tagslug) && !empty($tagslug)) ? $tagslug : "tag";
    	$single_slug = (isset($singleslug) && !empty($singleslug)) ? $singleslug : "post";
        $xipblogroutes = array(
	        	'xipblog-xipblog-module' => array(
	        	    'controller' =>  'archive',
	        	    'rule' =>        $main_slug.$postfix_slug,
	        	    'keywords' => array(),
	        	    'params' => array(
	        	        'fc' => 'module',
	        	        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'category',
	        	    )
	        	),
                'xipblog-archive-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$category_slug.'/{id}_{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'category',
                    )
                ),
                'xipblog-archive-aftrid-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$category_slug.'/{rewrite}_{id}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'category',
                    )
                ),
                'xipblog-archive-wid-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$category_slug.'/{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'category',
                    )
                ),
                'xipblog-tag-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$tag_slug.'/{id}_{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'tag',
                    )
                ),
                'xipblog-tag-aftrid-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$tag_slug.'/{rewrite}_{id}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'tag',
                    )
                ),
                'xipblog-tag-wid-module' => array(
                    'controller' =>  'archive',
                    'rule' =>        $main_slug.'/'.$tag_slug.'/{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id'   =>   array('regexp' => '[0-9]+', 'param' => 'id'),
                        'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'subpage_type' => 'post',
	        	        'page_type' => 'tag',
                    )
                ),
                'xipblog-single-module' => array(
                    'controller' =>  'single',
                    'rule' =>        $main_slug.'/'.$single_slug.'/{id}_{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id' =>   array('regexp' => '[0-9]+','param' => 'id'),
                        'rewrite' =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'page_type' => 'post',
                    )
                ),
                'xipblog-single-aftrid-module' => array(
                    'controller' =>  'single',
                    'rule' =>        $main_slug.'/'.$single_slug.'/{rewrite}_{id}'.$postfix_slug,
                    'keywords' => array(
                        'id' =>   array('regexp' => '[0-9]+','param' => 'id'),
                        'rewrite' =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'page_type' => 'post',
                    )
                ),
                'xipblog-single-wid-module' => array(
                    'controller' =>  'single',
                    'rule' =>        $main_slug.'/'.$single_slug.'/{rewrite}'.$postfix_slug,
                    'keywords' => array(
                        'id' =>   array('regexp' => '[0-9]+','param' => 'id'),
                        'rewrite' =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param' => 'rewrite'),
                    ),
                    'params' => array(
                        'fc' => 'module',
                        'module' => 'xipblog',
	        	        'page_type' => 'post',
                    )
                ),
            );
		return $xipblogroutes;
    }
    public static function GetLinkObject(){
    	if(!isset(self::$xiplinkobj) || empty(self::$xiplinkobj)){
    		$ssl = false;
    		if (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
    		    $ssl = true;
    		}
    		$protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
    		$useSSL = ((isset($ssl) && $ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? true : false;
    		$protocol_content = ($useSSL) ? 'https://' : 'http://';
    		self::$xiplinkobj = new Link($protocol_link, $protocol_content);
    	}
    	return self::$xiplinkobj;
    }
    public static function getBaseLink($id_shop = null, $ssl = null, $relative_protocol = false)
    {
        static $force_ssl = null;

        if ($ssl === null) {
            if ($force_ssl === null) {
                $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
            }
            $ssl = $force_ssl;
        }

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && $id_shop !== null) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }

        if ($relative_protocol) {
            $base = '//'.($ssl ? $shop->domain_ssl : $shop->domain);
        } else {
            $base = (($ssl) ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);
        }

        return $base.$shop->getBaseURI();
    }
    public static function getLangLink($id_lang = null, Context $context = null, $id_shop = null)
    {
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$id_shop) {
            $id_shop = $context->shop->id;
        }
        $allow = (int)Configuration::get('PS_REWRITING_SETTINGS');
        if ((!$allow && in_array($id_shop, array($context->shop->id,  null))) || !Language::isMultiLanguageActivated($id_shop) || !(int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop)) {
            return '';
        }
        if (!$id_lang) {
            $id_lang = $context->language->id;
        }
        return Language::getIsoById($id_lang).'/';
    }
	public static function XipBlogMainLink() {
		$id_shop = (int)Context::getcontext()->shop->id;
		$id_lang = (int)Context::getcontext()->language->id;
		$ssl = null;
		$relative_protocol = false;
		$url = self::getBaseLink($id_shop, $ssl, $relative_protocol).self::getLangLink($id_lang, null, $id_shop);
		return $url; 
	}
	public static function XipBlogLink($rule = 'xipblog-xipblog-module',$params = array()){
		$context = Context::getContext();
		$id_lang = (int)$context->language->id;
		$id_shop = (int)$context->shop->id;
		$mainurl = self::XipBlogMainLink();
		if(!isset(self::$dispatcherobj) || empty(self::$dispatcherobj)){
			self::$dispatcherobj = Dispatcher::getInstance();
		}
		$force_routes = (bool)Configuration::get('PS_REWRITING_SETTINGS');
        return $mainurl.self::$dispatcherobj->createUrl($rule,$id_lang,$params,$force_routes);
    }
    public static function XipBlogPostLink($params = array()){
    	$url_format = Configuration::get(self::$xipblogshortname."url_format");
    	if(isset($params['id']) && !isset($params['rewrite'])){
    		$params['rewrite'] = xippostsclass::get_the_rewrite($params['id']);
    	}
    	if(!isset($params['id']) && isset($params['rewrite'])){
    		$params['id'] = xippostsclass::get_the_id($params['rewrite']);
    	}
    	if(!isset($params['page_type'])){
    		$params['page_type'] = 'post';
    	}
    	if($url_format == 'preid_seo_url'){
    		$rule = 'xipblog-single-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'postid_seo_url') {
    		$rule = 'xipblog-single-aftrid-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'wthotid_seo_url') {
    		$rule = 'xipblog-single-wid-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'default_seo_url') {
    		return self::GetLinkObject()->getModuleLink("xipblog","single",$params);
    	}else{
    		$rule = 'xipblog-single-module';
    	}
    }
    public static function XipBlogTagLink($params = array()){
    	$url_format = Configuration::get(self::$xipblogshortname."url_format");
    	// if(isset($params['id']) && !isset($params['rewrite'])){
    	// 	$params['rewrite'] = xippostsclass::get_the_rewrite($params['id']);
    	// }
    	if(!isset($params['page_type'])){
    		$params['page_type'] = 'tag';
    	}
    	if(!isset($params['subpage_type'])){
    		$params['subpage_type'] = 'post';
    	}
    	if($url_format == 'preid_seo_url'){
    		$rule = 'xipblog-tag-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'postid_seo_url') {
    		$rule = 'xipblog-tag-aftrid-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'wthotid_seo_url') {
    		$rule = 'xipblog-tag-wid-module';
    		return self::XipBlogLink($rule,$params);
    	}elseif ($url_format == 'default_seo_url') {
    		return self::GetLinkObject()->getModuleLink("xipblog","archive",$params);
    	}else{
    		$rule = 'xipblog-tag-module';
    		return self::XipBlogLink($rule,$params);
    	}
    }
    public static function XipBlogCategoryLink($params = array()){
        $url_format = Configuration::get(self::$xipblogshortname."url_format");
        // if(isset($params['id']) && !isset($params['rewrite'])){
        // 	$params['rewrite'] = xippostsclass::get_the_rewrite($params['id']);
        // }
        if(!isset($params['page_type'])){
    		$params['page_type'] = 'category';
    	}
    	if(!isset($params['subpage_type'])){
    		$params['subpage_type'] = 'post';
    	}
        if($url_format == 'preid_seo_url'){
        	$rule = 'xipblog-archive-module';
        	return self::XipBlogLink($rule,$params);
        }elseif ($url_format == 'postid_seo_url') {
        	$rule = 'xipblog-archive-aftrid-module';
        	return self::XipBlogLink($rule,$params);
        }elseif ($url_format == 'wthotid_seo_url') {
        	$rule = 'xipblog-archive-wid-module';
        	return self::XipBlogLink($rule,$params);
        }elseif ($url_format == 'default_seo_url') {
        	return self::GetLinkObject()->getModuleLink("xipblog","archive",$params);
        }else{
        	$rule = 'xipblog-archive-module';
        	return self::XipBlogLink($rule,$params);
        }
    }
    /* xipblog::GetThemeName()  */
	public static function GetThemeName(){
		$theme_name = Configuration::get(self::$xipblogshortname."theme_name");
		if(isset($theme_name) && !empty($theme_name)){
			return $theme_name;
		}else{
			return "default";
		}
	}
	public function Register_Tabs()
	{
		$tabs_lists = array();
        $langs = Language::getLanguages();
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $save_tab_id = $this->RegisterParentTabs();
    	if(isset($this->all_tabs) && !empty($this->all_tabs)){
    		foreach ($this->all_tabs as $tab_list)
    		{
    		    $tab_listobj = new Tab();
    		    $tab_listobj->class_name = $tab_list['class_name'];
    		    if($tab_list['id_parent'] == 'parent'){
    		    	$tab_listobj->id_parent = $save_tab_id;
    		    }else{
    		    	$tab_listobj->id_parent = $tab_list['id_parent'];
    		    }
    		    if(isset($tab_list['module']) && !empty($tab_list['module'])){
    		    	$tab_listobj->module = $tab_list['module'];
    		    }else{
    		    	$tab_listobj->module = $this->name;
    		    }
    		    foreach($langs as $l)
    		    {
    		    	$tab_listobj->name[$l['id_lang']] = $this->l($tab_list['name']);
    		    }
    		    $tab_listobj->save();
    		}
    	}
        return true;
    }
    // Start Setting
    public function InstallSampleData()
    {
        $multiple_arr = array();
        $this->AllFields();
        foreach($this->fields_form as $key => $value){
        	if(empty($multiple_arr)){
        		$multiple_arr = $value['form']['input'];
        	}else{
            	$multiple_arr = array_merge($multiple_arr,$value['form']['input']);
        	}
        }
        // START LANG
		$languages = Language::getLanguages(false);
        if(isset($multiple_arr) && !empty($multiple_arr)){
            foreach($multiple_arr as $mvalue){
                if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
                   foreach($languages as $lang){
	                   	if(isset($mvalue['default_val'])){
	                    	${$mvalue['name'].'_lang'}[$lang['id_lang']] = $mvalue['default_val'];
	                   	}
                   }
                }
            }
        }
        // END LANG
        if(isset($multiple_arr) && !empty($multiple_arr)){
            foreach($multiple_arr as $mvalue){
                if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
                    Configuration::updateValue(self::$xipblogshortname.$mvalue['name'],${$mvalue['name'].'_lang'});
                }else{
                    if(isset($mvalue['name'])){
                    	if(isset($mvalue['default_val'])){
                        	Configuration::updateValue(self::$xipblogshortname.$mvalue['name'],$mvalue['default_val']);
                    	}
                    }
                }
            }
        }
        return true;
    }
    public function UnInstallSampleData()
    {
        $multiple_arr = array();
        $this->AllFields();
        foreach($this->fields_form as $key => $value){
            if(empty($multiple_arr)){
        		$multiple_arr = $value['form']['input'];
        	}else{
            	$multiple_arr = array_merge($multiple_arr,$value['form']['input']);
        	}
        }
        if(isset($multiple_arr) && !empty($multiple_arr)){
            foreach($multiple_arr as $mvalue){
                if(isset($mvalue['name'])){
                    Configuration::deleteByName(self::$xipblogshortname.$mvalue['name']);
                }
            }
        }
        return true;
    }
    public function AllFields()
    {
    	$xipblog_settings = array();
        include_once(dirname(__FILE__).$this->fields_arr_path);
        if($this->getConfigPath()){
        	include_once($this->getConfigPath());
        }
        if(isset($xipblog_settings) && !empty($xipblog_settings)){
        	foreach ($xipblog_settings as $xipblog_setting) {
        		$this->fields_form[]['form'] = $xipblog_setting;
        	}
        }
        return $this->fields_form;
    }
    public function AsignGlobalSettingValue(){
    	$xipblogsettings = $this->GetSettingsValueS();
    	$this->smarty->assignGlobal('xipblogsettings',$xipblogsettings);
    	return true;
    }
    public static function GetAllThemes(){
    	$results = array();
    	$theme_dirs = _PS_THEME_DIR_.'modules/'.xipblog_tpl_dir;
    	$module_dirs = _PS_MODULE_DIR_.xipblog_tpl_dir;

    	if(is_dir($theme_dirs)){
    		$scandir = scandir($theme_dirs);
    		$all_folders = array_diff($scandir, array('..', '.'));
    	}elseif(is_dir($module_dirs)){
    		$scandir = scandir($module_dirs);
    		$all_folders = array_diff($scandir, array('..', '.'));
    	}
    	if(isset($all_folders) && !empty($all_folders)){
    		$i = 0;
    		foreach ($all_folders as $folder) {
    			$results[$i]['id'] = $folder;
    			$results[$i]['name'] = ucwords($folder);
    			$i++;
    		}
    	}
    	return $results;
    }
    public function GetSettingsValueS()
    {
        $id_lang = Context::getcontext()->language->id;
        $multiple_arr = array();
        $xipblogsettings = array();
        $this->AllFields();
        foreach($this->fields_form as $key => $value){
            $multiple_arr = array_merge($multiple_arr,$value['form']['input']);
        }
        if(isset($multiple_arr) && !empty($multiple_arr)){
            foreach($multiple_arr as $mvalue){
                if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
                    $xipblogsettings[$mvalue['name']] = Configuration::get(self::$xipblogshortname.$mvalue['name'],$id_lang);
                }else{
                    if(isset($mvalue['name'])){
                        $xipblogsettings[$mvalue['name']] = Configuration::get(self::$xipblogshortname.$mvalue['name']);
                    }
                }
            }
        }
        return $xipblogsettings;
    }
    public function hookdisplayxipblogleft(){
    	// return 'i am left';
    }
    public function hookdisplayxipblogright(){
    	// return 'i am right';
    }
    public function hookdisplayheader(){
    	if(isset(self::$css_files) && !empty(self::$css_files)){
    		foreach (self::$css_files as $css) {
    			$this->context->controller->addCSS($this->_path."css/".$css);
    		}
    	}
    	if(isset(self::$js_files) && !empty(self::$js_files)){
    		foreach (self::$js_files as $js) {
    			$this->context->controller->addJS($this->_path."js/".$js);
    		}
    	}
    }
    public function GenerateImageThumbnail($select_image_type = 'all'){
    	$dir = _PS_MODULE_DIR_.self::$ModuleName.'/img/';
    	$GetAllImageTypes = xipimagetypeclass::GetAllImageTypes();
    	if($select_image_type == 'all' || $select_image_type == 'category'){
			// start category
			$categories = xipcategoryclass::GetCategories();
			if(isset($categories) && !empty($categories)){
				foreach ($categories as $category) {
					if(isset($category['category_img']) && !empty($category['category_img']) && file_exists($dir.$category['category_img'])){
						$ext = substr($category['category_img'], strrpos($category['category_img'], '.') + 1);
					    	if(isset($GetAllImageTypes) && !empty($GetAllImageTypes)){
						        foreach($GetAllImageTypes as $imagetype){
						        	ImageManager::resize($dir.$category['category_img'],$dir.$imagetype['name'].'-'.$category['category_img'],(int)$imagetype['width'],(int)$imagetype['height'],$ext);
						        }
							}
					}
				}
			}
			// End category
		}
		if($select_image_type == 'all' || $select_image_type == 'gallery' || $select_image_type == 'post'){
			$posts_count = xippostsclass::GetCategoryPostsCount();
			$all_posts = xippostsclass::GetCategoryPosts(NULL,1,$posts_count,'post','DESC');
		}
    	if($select_image_type == 'all' || $select_image_type == 'post'){
			// Start Post Image
			if(isset($all_posts) && !empty($all_posts)){
				foreach($all_posts as $all_post){
					if(isset($all_post['post_img']) && !empty($all_post['post_img']) && file_exists($dir.$all_post['post_img'])){
						$ext = substr($all_post['post_img'], strrpos($all_post['post_img'], '.') + 1);
					    	if(isset($GetAllImageTypes) && !empty($GetAllImageTypes)){
						        foreach($GetAllImageTypes as $imagetype){
						        	ImageManager::resize($dir.$all_post['post_img'],$dir.$imagetype['name'].'-'.$all_post['post_img'],(int)$imagetype['width'],(int)$imagetype['height'],$ext);
						        }
							}
					}
				}
			}
			// End Post Image
		}
    	if($select_image_type == 'all' || $select_image_type == 'gallery'){
			// Start gallery Image
			if(isset($all_posts) && !empty($all_posts)){
				foreach($all_posts as $all_post){
					if(isset($all_post['gallery']) && !empty($all_post['gallery'])){
						$gallery = @explode(",",$all_post['gallery']);
						if(isset($gallery) && !empty($gallery) && is_array($gallery)){
							foreach ($gallery as $gall) {
								if(file_exists($dir.$gall)){
									$ext = substr($gall, strrpos($gall, '.') + 1);
							    	if(isset($GetAllImageTypes) && !empty($GetAllImageTypes)){
								        foreach($GetAllImageTypes as $imagetype){
								        	ImageManager::resize($dir.$gall,$dir.$imagetype['name'].'-'.$gall,(int)$imagetype['width'],(int)$imagetype['height'],$ext);
								        }
									}
								}
							}
						}
					}
				}
			}
			// End gallery Image
		}
    }
    public function getContent()
    {
    	// $this->registerHook("displayBackOfficeTop");
    	// $this->registerHook("displayAdminAfterHeader");
    	if(Tools::isSubmit('submit_generateimage')){
        	$select_image_type = Tools::getValue('select_image_type');
        	$this->GenerateImageThumbnail($select_image_type);
        }
    	$this->context->controller->addJqueryPlugin('tagify');
        Configuration::updateValue('xipblogshortname',self::$xipblogshortname);
        $html = '';
        $multiple_arr = array();
        // START RENDER FIELDS
        $this->AllFields();
        // END RENDER FIELDS
        if(Tools::isSubmit('save'.$this->name)){
            foreach($this->fields_form as $key => $value){
                $multiple_arr = array_merge($multiple_arr,$value['form']['input']);
            }
            // START LANG
            $languages = Language::getLanguages(false);
            if(isset($multiple_arr) && !empty($multiple_arr)){
                foreach($multiple_arr as $mvalue){
                    if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
                       foreach($languages as $lang){
                        ${$mvalue['name'].'_lang'}[$lang['id_lang']] = Tools::getvalue($mvalue['name'].'_'.$lang['id_lang']);
                       }
                    }
                }
            }
            // END LANG
            if(isset($multiple_arr) && !empty($multiple_arr)){
                foreach($multiple_arr as $mvalue){
                    if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
                            Configuration::updateValue(self::$xipblogshortname.$mvalue['name'],${$mvalue['name'].'_lang'});
                    }else{
                        if(isset($mvalue['name'])){
                            Configuration::updateValue(self::$xipblogshortname.$mvalue['name'],Tools::getvalue($mvalue['name']));
                        }
                    }
                }
            }
            $helper = $this->SettingForm();
            $html_form = $helper->generateForm($this->fields_form);
            $html .= $this->displayConfirmation($this->l('Successfully Saved All Fields Values.'));
            $html .= $html_form;
        }else{
            $helper = $this->SettingForm();
            $html_form = $helper->generateForm($this->fields_form);
            $html .= $html_form;
        }
        return $html;
    }
    public function SettingForm() {
    	$languages = Language::getLanguages(false);
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->AllFields();
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        foreach ($languages as $lang)
                $helper->languages[] = array(
                        'id_lang' => $lang['id_lang'],
                        'iso_code' => $lang['iso_code'],
                        'name' => $lang['name'],
                        'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
                );
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save'.$this->name.'token=' . Tools::getAdminTokenLite('AdminModules'),
            )
        );
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'save'.$this->name;
        $multiple_arr = array();

        foreach($this->fields_form as $key => $value) {
        	if(empty($multiple_arr)){
        		if(isset($value['form']['input']) && !empty($value['form']['input'])){
        			$multiple_arr = $value['form']['input'];
        		}
        	}else{
        		if(isset($value['form']['input']) && !empty($value['form']['input'])){
        			$multiple_arr = array_merge($multiple_arr,$value['form']['input']);
        		}
        	}
        }
        foreach($multiple_arr as $mvalue){
            if(isset($mvalue['lang']) && $mvalue['lang'] == true && isset($mvalue['name'])){
               foreach($languages as $lang){
                    $helper->fields_value[$mvalue['name']][$lang['id_lang']] = Configuration::get(self::$xipblogshortname.$mvalue['name'],$lang['id_lang']);
               }
            }else{
                if(isset($mvalue['name'])){
                    $helper->fields_value[$mvalue['name']] = Configuration::get(self::$xipblogshortname.$mvalue['name']);
                }
            }
        }
        return $helper;
    }
    public function getConfigPath()
    {
    	$template = 'settings.php';
    	$themename = self::GetThemeName();
        if (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.xipblog::$ModuleName.'/views/templates/front/'.$themename.'/'.$template)) {
            return _PS_THEME_DIR_.'modules/'.xipblog::$ModuleName.'/views/templates/front/'.$themename.'/'.$template;
        } elseif (Tools::file_exists_cache(_PS_THEME_DIR_.'modules/'.xipblog::$ModuleName.'/views/templates/front/'.$template)) {
            return _PS_THEME_DIR_.'modules/'.xipblog::$ModuleName.'/views/templates/front/'.$template;
        } elseif (Tools::file_exists_cache(_PS_MODULE_DIR_.xipblog::$ModuleName.'/views/templates/front/'.$themename.'/'.$template)) {
            return _PS_MODULE_DIR_.xipblog::$ModuleName.'/views/templates/front/'.$themename.'/'.$template;
    	} elseif (Tools::file_exists_cache(_PS_MODULE_DIR_.xipblog::$ModuleName.'/views/templates/front/'.$template)) {
            return _PS_MODULE_DIR_.xipblog::$ModuleName.'/views/templates/front/'.$template;
        }
        return false;
    }
    // end settings
    /*  xipblog::UploadMedia('image'); */
    public static function UploadMedia($name,$dir=NULL)
    {
    	if($dir == NULL){
    		$dir = _PS_MODULE_DIR_.self::$ModuleName.'/img/';
    	}
		$file_name = false;
		if (isset($_FILES[$name]) && isset($_FILES[$name]['tmp_name']) && !empty($_FILES[$name]['tmp_name'])) {
			$ext = substr($_FILES[$name]['name'], strrpos($_FILES[$name]['name'], '.') + 1);
			$basename_file_name = basename($_FILES[$name]["name"]);
			$strlen = strlen($basename_file_name);
			$strlen_ext = strlen($ext);
			$basename_file_name = substr($basename_file_name,0,($strlen-$strlen_ext));
			$link_rewrite_file_name = Tools::link_rewrite($basename_file_name);
			$file_name = $link_rewrite_file_name.'.'.$ext;
			$path = $dir.$file_name;
			$GetAllImageTypes = xipimagetypeclass::GetAllImageTypes();
			if(!move_uploaded_file($_FILES[$name]['tmp_name'],$path)) {
				return false;
			}else{
				if(isset($GetAllImageTypes) && !empty($GetAllImageTypes)){
			        foreach($GetAllImageTypes as $imagetype){
			        	ImageManager::resize($path,$dir.$imagetype['name'].'-'.$file_name,(int)$imagetype['width'],(int)$imagetype['height'],$ext);
			        }
				}
				return $file_name;
			}
		}else{
			return $file_name;
		}
	}
    public static function BulkUploadMedia($name,$dir=NULL)
    {
    	if($dir == NULL){
    		$dir = _PS_MODULE_DIR_.self::$ModuleName.'/img/';
    	}
    	$results_imgs = array();
		if (isset($_FILES[$name]) && isset($_FILES[$name]['tmp_name']) && !empty($_FILES[$name]['tmp_name'])) {
			foreach ($_FILES[$name]['name'] as $fileskey => $filesvalue) {
				// start upload
			if (isset($_FILES[$name]) && isset($_FILES[$name]['tmp_name'][$fileskey]) && !empty($_FILES[$name]['tmp_name'][$fileskey])) {
					$ext = substr($_FILES[$name]['name'][$fileskey], strrpos($_FILES[$name]['name'][$fileskey], '.') + 1);
					$basename_file_name = basename($_FILES[$name]["name"][$fileskey]);
					$strlen = strlen($basename_file_name);
					$strlen_ext = strlen($ext);
					$basename_file_name = substr($basename_file_name,0,($strlen-$strlen_ext));
					$link_rewrite_file_name = Tools::link_rewrite($basename_file_name);
					$file_name = $link_rewrite_file_name.'.'.$ext;
					$path = $dir.$file_name;
					$GetAllImageTypes = xipimagetypeclass::GetAllImageTypes();
					if(move_uploaded_file($_FILES[$name]['tmp_name'][$fileskey],$path)) {
						if(isset($GetAllImageTypes) && !empty($GetAllImageTypes)){
					        foreach($GetAllImageTypes as $imagetype){
					        	ImageManager::resize($path,$dir.$imagetype['name'].'-'.$file_name,(int)$imagetype['width'],(int)$imagetype['height'],$ext);
					        }
						}
						$results_imgs[] = $file_name;
					}
				}
				// end upload
			}
			return $results_imgs;
		}else{
			return $results_imgs;
		}
	}
    public function hookexecute()
	{
		$results = array();
		$this->context->smarty->assign(array('results' => $results));
		return $this->display(__FILE__,'views/templates/front/xipblog.tpl');
	}
	public function InsertDummyData($categories,$class){
		$languages = Language::getLanguages(false);
	    if(isset($categories) && !empty($categories)){
	        $classobj = new $class();
	        foreach($categories as $valu){
	        	if(isset($valu['lang']) && !empty($valu['lang'])){
	        		foreach ($valu['lang'] as $valukey => $value){
	        			foreach ($languages as $language){
	        				if(isset($valukey)){
	        					$classobj->{$valukey}[$language['id_lang']] = isset($value) ? $value : '';
	        				}
	        			}
	        		}
	        	}
        		if(isset($valu['notlang']) && !empty($valu['notlang'])){
        			foreach ($valu['notlang'] as $valukey => $value){
        				if(isset($valukey)){
        					if($valukey == "id_shop"){
        						$classobj->{$valukey} = (int)Context::getContext()->shop->id;
        					}else{
        						$classobj->{$valukey} = $value;
        					}
        				}
        			}
        		}
	        	$classobj->add();
	        }
	    }
	}
	public function DummyData()
	{
	    $id_lang = (int)Context::getContext()->language->id;
	    $id_shop = (int)Context::getContext()->shop->id;
	    include_once(dirname(__FILE__).'/data/dummy_data.php');
	    $this->InsertDummyData($xipblog_imagetype,'xipimagetypeclass');
	    $this->InsertDummyData($xipblog_categories,'xipcategoryclass');
	    $this->InsertDummyData($xipblog_posts,'xippostsclass');
	    return true;
	}	
	public function hookdisplayAdminAfterHeader(){
		$data = @Tools::file_get_contents('http://xpert-idea.com/promotion/promotion.php');
		print $data;
	}	
}

// displayAdminListAfter


