
<?php /*
Template Name: Autologin API Template
*/

$salt = '';
$err_succ = array(
    'key'   => 0,
    'status' => 'failed'
);
$salt = 'SxvdhhipYePGaoPxrUDlHxhDMOuARFGaNbLsmEMDPmZYAKRCSYsONQRhejfPAifu';

function verify_token($p_token, $p_login){
   global $salt;
   global $err_succ; 
   $verified_token = sha1($salt . $p_login);
    if ($verified_token != $p_token) {
        $err_succ['status'] = 'verify failed'.$salt.$p_login;
        return false;
    }
    $err_succ['status'] = 'verify succeed';
    return true;
}

if( !isset($_POST) || !isset($_POST['token']) || !isset( $_POST['user_login'] )){    
    $err_succ['status'] = 'field not completed';

    $result = $err_succ;
    echo json_encode ($result);
    return;
}

global $wpdb;

// Check if we received a user_login from the POST, if yes - we sanitize it then save it to a variable
$user_login = isset( $_POST['user_login'] ) ? sanitize_text_field( $_POST['user_login'] ) : '';
$token = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';

function create_auto_login_user_if_not_exist($user_login){
    global $err_succ;
    global $wpdb;
    if( null == username_exists( $user_login ) ) {
        // Generate the password and create the user
        $password = wp_generate_password( 12, false );
        $user_id = wp_create_user( $user_login, $password, $user_login.'@abc.com' );
        // Set the nickname
        wp_update_user(
            array(
                'ID'          =>    $user_id,
                'nickname'    =>    $user_id
            )
        );
        // Set the role
        $user = new WP_User( $user_id );
        $user->set_role( 'contributor' );
        $hash_key = md5($user_id + rand(5, 15));

        foldLeftMenu($user_id);
        set_color_schema($user_id, 'blue');
        // Save the avatar(user_login) and key to the database
        $wpdb->insert(
                'wp_autologin', 
                array(
                    'avatar' => $user_login,
                    'random_key' => $hash_key
            )
        );
        $err_succ['key'] = $hash_key;
        $err_succ['status'] = 'success';
    } 
}

if(!verify_token($token, $user_login))
{
    $result = $err_succ;
    echo json_encode ($result);
    return;
}

if(  $_POST['action'] == 'get_login_key' ) {
    create_auto_login_user_if_not_exist($user_login);

    $user_random_key = $wpdb->get_var($wpdb->prepare("
            SELECT random_key FROM wp_autologin WHERE avatar = %s", $user_login) );
    
    $check_user_login = $wpdb->get_var($wpdb->prepare("
        SELECT COUNT(user_login) FROM wp_users WHERE user_login = '%s'", $user_login ) );
    
    // Check if the received user_login exists on the wp_users table
    if ($check_user_login > 0) {    
        
        if(empty($user_random_key)) {
    
            $hash_key = md5($user_login + rand(5, 15));
                    
            $wpdb->insert(
                    'wp_autologin', 
                    array(
                        'avatar' => $user_login,
                        'random_key' => $hash_key
                    )
                );
            
        } else {
            $hash_key = $user_random_key;
        }
        
        // Return the hash_key and set the status as success
        $err_succ['key'] = $hash_key;
        $err_succ['status'] = 'success';
            
    } else {
        $err_succ['status'] = 'failed';
    }
}

function foldLeftMenu($user_id){
        $user_fold = get_user_meta( $user_id, 'wp_user-settings', true );
        $exp_array = explode( "&", $user_fold );
        $exp_array[] = "mfold=f";
        $exp_array = implode( "&", $exp_array );
        update_user_meta( $user_id, 'wp_user-settings', $exp_array );
}

function set_color_schema($user_id, $color="blue"){
        $args = array(
        'ID' => $user_id,
        'admin_color' => $color
    );
    wp_update_user( $args );
}


if( $_POST['action'] == 'register' ) {
    create_user_if_not_exist($user_id);  
}


// Set the array to a variable
$result = $err_succ;

// JSON encode the result then send it back to the requesting client
echo json_encode ($result);

?>