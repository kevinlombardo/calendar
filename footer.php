<!-- footer -->

	MYSQL_CLOSE();

function monthname($i){
    switch($i){
        case "1":
            $monthname = "January"; break;
        case "2":
            $monthname = "February"; break;
        case "3":
            $monthname = "March"; break;
        case "4":
            $monthname = "April"; break;
        case "5":
            $monthname = "May"; break;
        case "6":
            $monthname = "June"; break;
        case "7":
            $monthname = "July"; break;
        case "8":
            $monthname = "August"; break;
        case "9":
            $monthname = "September"; break;
        case "10":
            $monthname = "October"; break;
        case "11":
            $monthname = "November"; break;
        case "12":
            $monthname = "December"; break;
        default:
            $monthname = "N/A"; break;
    }

    return $monthname;
}

function dayname($i){
    switch($i){
        case "1":
            $dayname = "Sunday"; break;
        case "2":
            $dayname = "Monday"; break;
        case "3":
            $dayname = "Tuesday"; break;
        case "4":
            $dayname = "Wednesday"; break;
        case "5":
            $dayname = "Thursday"; break;
        case "6":
            $dayname = "Friday"; break;
        case "7":
            $dayname = "Saturday"; break;
        default:
            $dayname = "N/A"; break;
    }

    return $dayname;
}



