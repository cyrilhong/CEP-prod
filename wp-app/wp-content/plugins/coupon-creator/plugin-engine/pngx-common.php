<?php
/*
Plugin Name: Plugin Engine
Description: A plugin framework to be embeded in the core plugin
Version: 2.5
Author: Jessee Productions, LLC
Author URI: https://jesseeproductions.com/
Text Domain: plugin-engine
License: GPLv2 or later
*/
/*
Copyright 2009-2017 by Jessee Productions, LLC and the contributors
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
// the main plugin class
require_once dirname( __FILE__ ) . '/src/Pngx/Main.php';
Pngx__Main::instance();