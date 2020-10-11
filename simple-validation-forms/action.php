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
    foreach ($rules as $ruleName => $ruleValue) {
      switch($ruleName) {
        case 'min':
          if (strlen($response['data'][$fieldName]) < $ruleValue) {
            $validationFields['validation_fields'][$fieldName] = [
              'message' => get_validation_message_body_from_template('The {field} must have at {min_value}', [ 'field' => $fieldName, 'min_value' => $ruleValue ])
            ];
          }
        break;
        case 'max':
          if (strlen($response['data'][$fieldName]) > $ruleValue) {
            $validationFields['validation_fields'][$fieldName] = [
              'message' => get_validation_message_body_from_template('The {field} must be less {max_value}', [ 'field' => $fieldName, 'max_value' => $ruleValue ])
            ];
          }
        break;
      }
    }
  }

  $responseValidated = array_merge($response, $validationFields);

  return $responseValidated;
}

$response = set_response('POST');

$response = set_response_validation_body($response, [
  'name' => [
    'min' => 4,
    'max' => 12
  ],
  'lastname' => [
    'min' => 3
  ]
]);

$response = response_validate($response);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Forms</title>
</head>
<body>
</body>
</html>