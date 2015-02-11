#!/usr/bin/php
<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/
/*
 * File Contributed By: Open Source Consulting, S.A.   San Jose, Costa Rica.
 * http://osc.co.cr
 */

// Calculates percent complete statistics for all locales.
// Must be run from tools/i18n directory.

   $root_dir = '../../interface/locale';
   if( count( $argv ) > 1 ) {
      $root_dir = $argv[1];
   }

   $d = opendir( $root_dir );
   
   if( $d ) {
   
      echo "calculating locale statistics...\n";

      $outpath = $root_dir . '/' . 'locale_stats.txt';
      $fh = fopen( $outpath, 'w' );
      
      $ignore_dirs = array( '.', '..', 'CVS' );
      while (false !== ($file = readdir($d))) {
         if( is_dir( $root_dir . '/' . $file ) && !in_array( $file, $ignore_dirs) ) {
            $stats = calcStats( $root_dir, $file );
            $pct = $stats['pct_complete'];
            $team = $stats['team'];
            fwrite( $fh, "$file|$pct|$team\n" );
         }
      }
      closedir( $d );
      
      fclose( $fh );
      
      echo "done. stats saved in $outpath\n";
   }

   function calcStats( $root_dir, $locale ) {
      $messages = 0;
      $translations = 0;
      $fuzzy = 0;
      
      $team = '';
   
      $path = $root_dir . '/' . $locale . '/LC_MESSAGES/messages.po';
      // echo "<li><b>$path</b>";
      if( file_exists( $path ) ) {
         $lines = file( $path );
         
         $in_msgid = false;
         $in_msgstr = false;
         $found_translation = false;
         $found_msg = false;
         foreach( $lines as $line ) {
            // ignore comment lines
            if( $line[0] == '#' ) {
               continue;
            }
            
            // Parse out the contributors.
            if( strstr( $line, '"Language-Team: ' ) ) {
               $endpos = strpos( $line, '\n' );
               if( $endpos === false ) {
                  $endpos = strlen( $line ) - 2;
               }
               $len = strlen('"Language-Team: ');
               $field = substr( $line, $len, $endpos - $len );
               $names = explode( ',', $field );
               foreach( $names as $name ) {
                  if( $name != 'none' ) {
                     if( $team != '' ) {
                        $team .= ',';
                     }
                     $team .= trim( $name );
                  }
               }
            }
         
            if( strstr( $line, 'msgid "' ) ) {
               $in_msgid = true;
               $in_msgstr = false;
               $found_msg = false;
               $found_translation = false;
            }
            if( $in_msgid && !$found_msg && strstr( $line, '"' ) && !strstr( $line, '""' ) ) {
               // echo "<li>msgid: $line";
               $found_msg = true;
               $messages ++;
            }
            else if( strstr($line, 'msgstr "') ) {
               $in_msgstr = true;
               $in_msgid = false;
            }
            if( $in_msgstr && $found_msg && !$found_translation ) {
               if( strstr( $line, '"' ) && !strstr( $line, '""' ) ) {
                  // echo "<li>msgstr: $line";
                  $translations ++;
                  $found_translation = true;
               }
            }
            else if( strstr( $line, '#, fuzzy' ) ) {
               $fuzzy ++;
            }
         }
      }
      $translations -= $fuzzy;
      $pct_complete = $messages ? (int)(($translations / $messages) * 100) : 0;

      return array( 'pct_complete' => $pct_complete, 'team' => $team );
   }
 
 
?>
