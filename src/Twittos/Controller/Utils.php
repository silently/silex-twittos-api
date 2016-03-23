<?php
namespace Twittos\Controller;

class Utils {
  // Utils
  static public function formatErrors($errors) {
    if (count($errors) > 0) {
      $output = [ "errors" => []];
      foreach ($errors as $error) {
        array_push($output["errors"], ["source" => $error->getPropertyPath(), "detail" => $error->getMessage()]);
      }
      return $output;
    }
    return null;
  }
}
