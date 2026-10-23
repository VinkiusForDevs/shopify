<?php
/**
 * Visitor-consent repository (Joomla flat-file port).
 */
namespace VinkiusConnector\Models;

defined('ABSPATH') || exit;

final class ConsentRepo {
  const SCOPES = array('content.read','content.write','users.subscribe','media.upload');

  public static function table() { return 'vinkius_consents'; }

  public static function record($external_id, $wp_user_id, array $scopes, $purpose = 'agent_action', $ip = '') {
    $external_id = trim(strip_tags((string)$external_id));
    if ($external_id === '') return 0;
    $valid = array_values(array_intersect($scopes, self::SCOPES));
    $valid = empty($valid) ? array('content.read') : $valid;
    $rows = vc_get_store('vinkius_consents', array());
    if (!is_array($rows)) $rows = array();
    $now = gmdate('Y-m-d H:i:s');
    $found = null;
    foreach ($rows as $i => $r) {
      if (($r['external_id'] ?? '') === $external_id) { $found = $i; break; }
    }
    if ($found !== null) {
      $rows[$found]['wp_user_id'] = (int)$wp_user_id;
      $rows[$found]['scopes'] = json_encode($valid);
      $rows[$found]['purpose'] = trim(strip_tags((string)$purpose));
      $rows[$found]['ip_hash'] = self::hash_ip($ip);
      $rows[$found]['revoked_at'] = null;
      $rows[$found]['created_at'] = $now;
      vc_set_store('vinkius_consents', $rows);
      return (int)$rows[$found]['id'];
    }
    $id = count($rows) + 1;
    $rows[] = array(
      'id' => $id,
      'external_id' => $external_id,
      'wp_user_id' => (int)$wp_user_id,
      'scopes' => json_encode($valid),
      'purpose' => trim(strip_tags((string)$purpose)),
      'ip_hash' => self::hash_ip($ip),
      'created_at' => $now,
      'revoked_at' => null,
    );
    vc_set_store('vinkius_consents', $rows);
    return $id;
  }

  public static function revoke($row_id) {
    $rows = vc_get_store('vinkius_consents', array());
    if (!is_array($rows)) return false;
    foreach ($rows as $i => $r) {
      if ((int)$r['id'] === (int)$row_id) {
        $rows[$i]['revoked_at'] = gmdate('Y-m-d H:i:s');
        vc_set_store('vinkius_consents', $rows);
        return true;
      }
    }
    return false;
  }

  public static function find($external_id) {
    $rows = vc_get_store('vinkius_consents', array());
    if (!is_array($rows)) return null;
    foreach ($rows as $r) {
      if (($r['external_id'] ?? '') === trim(strip_tags((string)$external_id))) {
        return $r;
      }
    }
    return null;
  }

  public static function list_all($limit = 100) {
    $rows = vc_get_store('vinkius_consents', array());
    if (!is_array($rows)) return array();
    usort($rows, function($a,$b){ return strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''); });
    return array_slice($rows, 0, max(1,min((int)$limit,500)));
  }

  public static function is_active($external_id, $scope) {
    $row = self::find($external_id);
    if (!$row || !empty($row['revoked_at'])) return false;
    $scopes = json_decode((string)$row['scopes'], true);
    return is_array($scopes) && in_array($scope, $scopes, true);
  }

  public static function revoke_token($row_id) {
    $secret = (string)\VinkiusConnector\Options::get('site_secret');
    return substr(hash_hmac('sha256', 'revoke:' . (int)$row_id, $secret), 0, 32);
  }

  public static function resolve_revoke_token($token) {
    $token = substr((string)$token, 0, 32);
    if ($token === '') return 0;
    $secret = (string)\VinkiusConnector\Options::get('site_secret');
    $rows = vc_get_store('vinkius_consents', array());
    if (!is_array($rows)) return 0;
    foreach ($rows as $r) {
      $row_token = substr(hash_hmac('sha256', 'revoke:' . (int)$r['id'], $secret), 0, 32);
      if (hash_equals($row_token, $token)) return (int)$r['id'];
    }
    return 0;
  }

  private static function hash_ip($ip) {
    $ip = trim((string)$ip);
    if ($ip === '') return '';
    return substr(hash_hmac('sha256', $ip, wp_salt('auth')), 0, 64);
  }
}