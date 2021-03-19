<?php

class wpForoPMsTools{
    public function get_human_time($date, $type){
        if(is_numeric($date)) $date = date_i18n( 'Y-m-d H:i:s', $date);
        
        $td = wpforo_phrase('Today', false);
        $pm_time = wpforo_date_raw( $date, 'n/j, H:i', false );
        $current_year = wpforo_date_raw( current_time( 'timestamp', 1 ), 'Y', false );
        $pm_year = wpforo_date_raw( $date, 'Y', false );
        
        $current_day = wpforo_date_raw( current_time( 'timestamp', 1 ), 'j', false );
        $pm_day = wpforo_date_raw( $date, 'j', false );
        
        if( $current_day != $pm_day  ) $td = wpforo_phrase('Yesterday', false);
        
        $diff = (int) abs( current_time( 'timestamp', 1 ) - strtotime($date) );
        if( $current_year != $pm_year ){
            $last_human_time = wpforo_date_raw( $date, 'j/n/y', false );
            $abbr_title = wpforo_date_raw( $date, 'F j, Y', false );;
        }elseif( $diff <= HOUR_IN_SECONDS ){
            $pm_time = $last_human_time = wpforo_date_raw($date, 'ago', false);
            $abbr_title = $td;
        }elseif( $diff <= DAY_IN_SECONDS ){
            $last_human_time = wpforo_date_raw($date, 'ago', false);
            $abbr_title = $td;
            $pm_time = wpforo_date_raw($date, 'H:i', false);
        }elseif( $diff <= 2 * DAY_IN_SECONDS ){
            $last_human_time = wpforo_date_raw($date, 'ago', false);
            $abbr_title = $td;
        }elseif( $diff < WEEK_IN_SECONDS ){
            $last_human_time = wpforo_date_raw( $date, 'D H:s', false );
            $abbr_title = wpforo_date_raw( $date, 'l', false );
        }else{
            $last_human_time = wpforo_date_raw( $date, 'M j', false );
            $abbr_title = wpforo_date_raw( $date, 'F j', false );
        }
        
        switch($type){
            case 'last_human_time':
                $return = $last_human_time;
            break;
            case 'abbr_title':
                $return = $abbr_title;
            break;
            case 'pm':
                $return = ( $diff <= MINUTE_IN_SECONDS ? wpforo_phrase('Now', false) : $pm_time );
            break;
            default:
                $return = $pm_time;
            break;
        }

        return $return;
    }
}