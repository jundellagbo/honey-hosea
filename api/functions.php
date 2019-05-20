<?php

require_once "setup.php";

function remove_ ( $sp, $id ) {
    global $conn;
    $sp = "CALL " . $sp . "(" . $id . ")";
    $stmt = $conn->prepare( $sp );
    $stmt->execute();
    response([ "response" => "removed" ]);
}

function retrieve_ ( $sp, $param = null ) {
    global $conn;
    $spparams = "";
    if( $param ) {
        foreach( $param as $x ) {
            $spparams .= $x . ",";
        }
        $spparams = rtrim( $spparams, "," );
    }
    $sp = "CALL " . $sp . "($spparams)";
    $stmt = $conn->prepare( $sp );
    $stmt->execute();
    $rets = array();
    while( $result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rets[] = $result;
    }
    return $rets;
}


function store_ ( $sp, $params ) {
    global $conn;
    $spparams = null;
    foreach( $params as $x ) {
        $spparams .= $x . ",";
    }
    $spparams = rtrim( $spparams, "," );
    $sp = "CALL " . $sp . "(" . $spparams . ")";
    $stmt = $conn->prepare( $sp );
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Students API
 */

function students_retrieve () {
    $students = retrieve_(
        "sp_students_get"
    );
    response([ "students" => $students ]);
}

function requirementDefault( $student ) {
    global $conn;
    $data = retrieve_(
        "sp_meta_retrieve",
        [ toString("requirement") ]
    );

    if( count( $data ) ) {
        $query = "INSERT INTO requirements(requirement, status, studentsid) VALUES";    
        $counter = 0;
        foreach( $data as $x ) {
            $counter++;
            $query .= "(" . toString($x["metaname"]) . ", " . $x["metakey"] . ", " . $student["id"] . ")";
            if( $counter != count( $data ) ) {
                $query .= ",";
            }
        }
        $stmt = $conn->prepare( $query );
        $stmt->execute();
    }
}

function students_store () {
    $id = store_(
        "sp_students_store", 
    [
        post()->id, 
        toString( post()->fname ), 
        toString( post()->mname ),
        toString( post()->lname ),
        toString( isset(post()->extension) ? post()->extension : "" ),
        toString( post()->dateofbirth ),
        toString( post()->sex ),
        toString( post()->street ),
        toString( post()->barangay ),
        toString( post()->city ),
        toString( post()->zip ),
        toString( post()->gname ),
        toString( post()->gmname ),
        toString( post()->glname ),
        toString( post()->tel ),
        toString( post()->cel ),
        post()->status
    ]);

    if( post()->id == 0 ) {
        requirementDefault( $id );
    }

    response(["response" => $id]);
}

/**
 * Requirements API
 */

function requirements_retrieve ( $id ) {
    $requirements = retrieve_(
        "sp_requirements_retrieve",
        [ $id ]
    );

    response([ "requirements" => $requirements ]);
}

function requirements_store() {
    $id = store_(
        "sp_requirements_store",
        [
            post()->id,
            toString( post()->requirement ),
            post()->studentsid,
            post()->status
        ]
    );

    response([ "response" => $id ]);
}

/**
 * Class API
 */

function class_store() {
    $id = store_(
        "sp_class_store",
        [
            post()->id,
            toString( post()->classname )
        ]
    );

    response([ "response" => $id ]);
}

function class_retrieve() {
    $classes= retrieve_(
        "sp_class_retrieve"
    );
    response([ "classes" => $classes ]);
}

/**
 * Section API
 */

 function section_store() {
    $id = store_(
         "sp_section_store",
         [
            post()->id,
            toString( post()->sectionname ),
            post()->classid,
            post()->average
         ]
    );
    response([ "response" => $id ]);
 }

 function section_retrieve( $id ) {
    $sections = retrieve_(
        "sp_section_retrieve",
        [ $id ]
    );
    response([ "sections" => $sections ]);
 }


 /**
  * META API
  */

  function meta_store() {
    $id = store_(
         "sp_meta_store",
         [
            post()->id,
            toString( post()->metaname ),
            post()->metakey,
            toString( post()->metatype )
         ]
    );
    response([ "response" => $id ]);
  }

function meta_retrieve( $type ) {
    $metas = retrieve_(
        "sp_meta_retrieve",
        [ toString( $type ) ]
    );
    response([ "metas" => $metas ]);
}

function records_autocomplete() {
    $classes = retrieve_(
        "sp_class_retrieve"
    );
    $data = array();
    foreach( $classes as $x ) {
        $x["sections"] = retrieve_("sp_section_retrieve", [ $x["id"] ]);
        $data[] = $x;
    }
    response( [
        "items" => [
            "class" => $data,
            "teachers" => retrieve_("sp_meta_retrieve",[ toString("teacher") ]),
            "subjects" => retrieve_("sp_meta_retrieve",[ toString("subject") ])
        ]
    ] );
}

function records_store() {
    $id = store_(
        "sp_records_store",
        [
           post()->id,
           post()->studentsid,
           toString( post()->records )
        ]
   );
   response([ "response" => $id ]);
}

function records_retrieve( $id ) {
    $records = retrieve_(
        "sp_records_retrieve",
        [ $id ]
    );
    response([ "records" => $records ]);
}

/**
 * Enrollments API
 */

 function enrollment_retrieve( $id ) {
    $enrollments = retrieve_(
        "sp_enrollment_retrieve",
        [ $id ]
    );

    $classes = retrieve_(
        "sp_class_retrieve"
    );

    response([ "enrollments" => $enrollments, "classes" => $classes ]);
 }

 function enrollment_store() {
     $id = store_(
         "sp_enroll_student",
         [
            post()->studentsid,
            toString( post()->schoolyear ),
            post()->classid
         ]
    );

    response([ "response" => $id ]);
 }

 /**
  * Login Authentication
  */

 function auth_login() {
    $login = store_(
        "sp_user_login",
        [
            toString( post()->username ),
            toString( post()->password )
        ]
    );
    response( ["response" => $login ]);
 }

 function user_settings() {
     $save = store_(
         "sp_user_store",
         [
             post()->id,
             toString( post()->fname ),
             toString( post()->mname ),
             toString( post()->lname ),
             toString( post()->username ),
             isset( post()->password ) ? toString( post()->password ) : "''",
             post()->roleId,
             post()->status
         ]
     );
     response( ["response" => $save ]);
 }

function user_retrieve( $id ) {
    $users = retrieve_("sp_user_retrieve", [ $id ]);
    response(["users" => $users]);
}

/**
 * Accountant
 */

function accountant_pricing() {
    response([
        "books" => retrieve_("sp_class_retrieve"),
        "tuition" => retrieve_("sp_pricing_get", [1]),
        "misc" => retrieve_("sp_pricing_get", [2])
    ]);
}

function accountant_pricing_store() {
    $save = store_(
        "sp_pricing_set",
        [
            post()->id,
            post()->price,
            !isset( post()->discount ) || post()->discount == "" ? 0 : post()->discount,
            !isset( post()->discount ) || post()->discount == "" ? toString("") : toString(post()->availability),
            toString( post()->other )
        ]
    );
    response(["response" => $save]);
}

function accountant_book_pricing() {
    $save = store_(
        "sp_class_books_pricing",
        [
            post()->id,
            post()->bookprice
        ]
    );
    response(["response" => $save]);
}

function accountant_statement( $id ) {
    $statement = retrieve_( "sp_statement_of_account", [ $id ] );

    if( count( $statement ) ) {

        $related = retrieve_( "sp_statement_related", [ $statement[0]["e_id"], $statement[0]["s_id"] ] );
        $payments = retrieve_( "sp_payments_retrieve", [ $id ] );
        $transactions = retrieve_( "sp_transaction_get", [ $id ] );

        response([
            "statement" => $statement[0],
            "related" => $related,
            "payments" => $payments,
            "transactions" => $transactions
        ]);

    } else {
        response(["not_found" => true]);
    }
}

function accountant_student_enrolled( $id ) {
    $save = store_( "sp_enroll", [ $id ] );
    response(["response" => "success"]);
}

function transac_store() {
    $transaction = store_(
        "sp_transaction_store",
        [
            post()->eid,
            toString( post()->transactions ),
            toString( post()->payer ),
            toString( post()->payor )
        ]
    );
    
    $getTransac = json_decode( post()->transactions, true );
    
    foreach( $getTransac as $row ) {
        store_(
            "sp_payment_pay",
            [ 
                $row["id"],
                $row["balance"]
            ]
        );
    }

    response(["response" => $transaction]);
}

function payment_new() {
    $payment = store_(
        "sp_payment_store",
        [
            post()->eid,
            toString( post()->payment ),
            post()->value
        ]
    );

    response(["response" => $payment]);
}