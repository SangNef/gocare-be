<?php

namespace App\Helper;

use Dwij\Laraadmin\Helpers\LAHelper;
use Illuminate\Support\Facades\Mail;
use Transliterator;

class CustomLAHelper extends \Dwij\Laraadmin\Helpers\LAHelper
{
    // LAHelper::print_menu($menu)
    public static function print_menu($menu, $active = false)
    {
        if (
            auth()->check()
            && auth()->user()->haveRoleMustBeExcludeFromRoutes()
            && in_array($menu->url, StoreOwnerHelper::excludeRoutes())
        ) {
            return;
        }

        $childrens = \Dwij\Laraadmin\Models\Menu::where("parent", $menu->id)->orderBy('hierarchy', 'asc')->get();

        $treeview = "";
        $subviewSign = "";
        if (count($childrens)) {
            $treeview = " class=\"treeview\"";
            $subviewSign = '<i class="fa fa-angle-left pull-right"></i>';
        }
        $active_str = '';
        if ($active) {
            $active_str = 'class="active"';
        }

        $str = '<li' . $treeview . ' ' . $active_str . '><a href="' . url(config("laraadmin.adminRoute") . '/' . $menu->url) . '"><i class="fa ' . $menu->icon . '"></i> <span>' . static::real_module_name($menu->name) . '</span> ' . $subviewSign . '</a>';

        if (count($childrens)) {
            $str .= '<ul class="treeview-menu">';
            foreach ($childrens as $children) {
                $str .= static::print_menu($children);
            }
            $str .= '</ul>';
        }
        $str .= '</li>';
        return $str;
    }

    public static function real_module_name($name)
    {
        $name = trans('module.' . str_replace('_', ' ', strtolower($name)));

        return $name;
    }

    public static function removeAccents($str)
    {
        return Transliterator::create('Latin-ASCII;')->transliterate($str);
    }

    public static function sendEmail($html, $mail, $subject, $name)
    {
        return Mail::send([], [], function ($message) use($html,$mail,$subject,$name) {
            $message->to($mail, $name)->subject($subject)->setBody($html, 'text/html');;
        });

    }
}
