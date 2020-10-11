<?php

function set_response($method) {
  if ($method == 'POST') {
    return [
      'name' => $_POST['name'] ?? htmlspecialchars($_POST['name']),
      'lastname' => $_POST['lastname'] ?? htmlspecialchars($_POST['lastname']),
      'age' => $_POST['age'] ?? intval($_POST['age'])
    ];
  }
}

function set_response_validation_body($response, $rules) {
  $validation = [
    'rules' => $rules
  ];

  $data = [
    'data' => $response
  ];

  $response = array_merge($data, $validation);

  return $response;
}

function get_validation_message_body_from_template($template, $fields) {
  // extract interpolation values from template
  preg_match_all("/(?<attr>{\w+})/", $template, $matches, PREG_OFFSET_CAPTURE);
  $attributes = $matches['attr'];
  $newAttributes = [
    'replacelist' => []
  ];

  foreach ($attributes as $key => $value) {
    $attrName = $value[0];
    $init_pos = $value[1];
    $end_pos = $init_pos + strlen($attrName);

    $attrsNameWithoutDelim = preg_split("/[{?|}?]/", $attrName);
    $attrNameWithoutDelim = join(null, $attrsNameWithoutDelim);
    $value = $fields[$attrNameWithoutDelim];

    $newAttributes[$attrNameWithoutDelim] = [
      'init_pos' => $init_pos,
      'end_pos' => $end_pos,
      'value' => $value
    ];

    $newAttributes['replacelist'] = array_merge($newAttributes['replacelist'], [ $attrName => $value ]);

    if (empty($newAttributes['content'])) {
      $newAttributes['content'] = $template;
    }

    $newAttributes['content'] = strtr($newAttributes['content'], $newAttributes['replacelist']);
  }

  return $newAttributes;
}

function response_validate($response) {
  $validationFields = [
    'validation_fields' => []
  ];

  foreach ($response['rules'] as $fieldName => $rules) {
    $fieldValue = $response['data'][$fieldName];

    foreach ($rules as $ruleName => $ruleValue) {
      switch($ruleName) {
        case 'required':
          if ($ruleValue) {
            if (empty($fieldValue)) {
              $validationFields['validation_fields'][$fieldName] = [
                'message' => get_validation_message_body_from_template('The {field} is required.', [ 'field' => $fieldName ])
              ];
            }
          }
        break;
        case 'min':
          if (strlen($fieldValue) < $ruleValue) {
            $validationFields['validation_fields'][$fieldName] = [
              'message' => get_validation_message_body_from_template('The {field} must have at {min_value}.', [ 'field' => $fieldName, 'min_value' => $ruleValue ])
            ];
          }
        break;
        case 'max':
          if (strlen($fieldValue) > $ruleValue) {
            $validationFields['validation_fields'][$fieldName] = [
              'message' => get_validation_message_body_from_template('The {field} must be less {max_value}.', [ 'field' => $fieldName, 'max_value' => $ruleValue ])
            ];
          }
        break;
        case 'is_int':
          if ($ruleValue) {
            if (!ctype_digit($fieldValue)) {
              $validationFields['validation_fields'][$fieldName] = [
                'message' => get_validation_message_body_from_template('The {field} must be integer.', [ 'field' => $fieldName ])
              ];
            }
          }
        break;
      }
    }
  }

  $responseValidated = array_merge($response, $validationFields);

  return $responseValidated;
}

function get_validation_message($response, $field) {
  if (empty($response['validation_fields'][$field])) {
    return null;
  }

  return $response['validation_fields'][$field]['message']['content'];
}

function get_response_data($response, &$errors, $field) {
  $message = get_validation_message($response, $field);

  if (!empty($message)) {
    array_push($errors, $message);
    return null;
  }

  return $response['data'][$field];
}

$response = set_response('POST');
$response = set_response_validation_body($response, [
  'name' => [
    'required' => true
  ],
  'lastname' => [
    'required' => true,
    'min' => 3,
    'max' => 12
  ],
  'age' => [
    'is_int' => true,
    'required' => true
  ]
]);
$response = response_validate($response);



/*
structure:
data
rules
validation_rules:
  name:
    message:
      content
  lastname:
    message:
      content
*/

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Forms</title>
</head>
<body>
  <?php $errors = [] ?>
  <?php $name = get_response_data($response, $errors, 'name') ?>
  <?php $lastname = get_response_data($response, $errors, 'lastname') ?>
  <?php $age = get_response_data($response, $errors, 'age') ?>

  <?php if (count($errors) > 0): ?>
    <?php foreach($errors as $error): ?>
    <?php echo "<h3>$error</h3>" ?>
    <?php endforeach; ?>
  <?php else: ?>
    <?php echo "<h1>Welcome, $name!</h1>" ?>
    <?php echo "<h2>Your last name is $lastname</h2>" ?>
    <?php echo "<small>You are $age old</small>" ?>
  <?php endif; ?>
</body>
</html>