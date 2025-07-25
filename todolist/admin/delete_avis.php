<?php\nsession_start();\nrequire_once '../db.php'; // Adjust path as needed for admin directory\n\nheader('Content-Type: application/json');\n\n// Optional: Restrict access to admin roles if necessary\n// if (!isset(['user_id']) || ['id_role'] != 1) { // Assuming 1 is admin role\n//     echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);\n//     exit();\n// }\n\nif (empty(['HTTP_X_REQUESTED_WITH']) || strtolower(['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {\n    // Not an AJAX request, or direct access. Deny.\n    echo json_encode(['success' => false, 'message' => 'Direct access is forbidden.']);\n    exit();\n}\n\nif (['REQUEST_METHOD'] === 'POST') {\n     = filter_input(INPUT_POST, 'id_avis', FILTER_VALIDATE_INT);\n\n    if (!) {\n        echo json_encode(['success' => false, 'message' => 'Invalid Avis ID.']);\n        exit();\n    }\n\n    try {\n         = ->prepare(\
DELETE
FROM
avis
WHERE
id_avis
=
?\);\n        ->execute([]);\n\n        if (->rowCount() > 0) {\n            echo json_encode(['success' => true, 'message' => 'Avis deleted successfully.']);\n        } else {\n            echo json_encode(['success' => false, 'message' => 'Avis not found.']);\n        }\n    } catch (PDOException ) {\n        error_log(\Database
error
deleting
avis:
\ . ->getMessage());\n        echo json_encode(['success' => false, 'message' => 'Database error occurred.', 'error' => ->getMessage()]);\n    }\n} else {\n    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);\n}\n?>
