<?php
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/include/checkAuth.php');

$user = $pixapi->user->info();
$num = 0;
$type = 'checkbox';
if (1 != $user['is_vip']) {
    $type = 'radio';
}
$query2 = '';
$query = $_GET['query'];
$post = [];
$groups = $pixapi->friend->subscriptionGroups->search();

foreach ($groups['subscription_groups'] as $group) {
    if (!$_POST['query_' . $group['id']]) {
        continue;
    }
    $post[] = $_POST['query_' . $group['id']];
}

foreach ($post as $result) {
    if ((count($post) - 1) > $num) {
        $query2 .= $result . ',';
    } else {
        $query2 .= $result;
    }
    $num++;
}

if ($query and $query2) {
    $response = $pixapi->friend->subscriptions->join_subscription_group($query, ['group_ids' => $query2]);
}
?>
<!DOCTYPE html>
<html>
<head>
<?php require_once(__DIR__ . '/include/header.php'); ?>
</head>
<body>
<div class="container">
    <?php require_once(__DIR__ . '/include/top.php'); ?>
    <h1 class="page-header">加入訂閱群組</h1>
    <h3>呼叫方式</h3>
    <pre>$pixapi->friend->subscriptions->join_subscription_group($name, $group_ids = array());</pre>
    <div class="panel panel-primary">
      <div class="panel-heading">實際測試</div>
      <div class="alert alert-info">非VIP只能將使用者加入一個訂閱群組</div>
      <div class="panel-body">
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="query">訂閱帳號(必選)</label>
            <div class="col-sm-4">
            <select class="form-control" id="query" name="query" onchange="location.href=this.options[this.selectedIndex].value">
              <option value="">請選擇</option>
<?php
$subscriptions = $pixapi->friend->subscriptions->search();
foreach ($subscriptions['subscriptions'] as $subscription) {
    // 非 VIP 只能將使用者加入一個訂閱群組，若該使用者已有群組則不顯示
    if (1 != $user['is_vip'] and $subscription['groups']) {
        continue;
    }
    if ($subscription['user']['name']) {
?>
              <option value="?query=<?= $subscription['user']['name'] ?>" <?= ($query == $subscription['user']['name']) ? 'selected' : ''; ?>><?= $subscription['user']['name'] ?></option>
<?php
    }
}
?>
            </select>
            </div>
          </div>
<?php if ('' != $query) { ?>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="query2">加入群組(必選)</label>
            <div class="col-sm-4">
<?php
foreach ($groups['subscription_groups'] as $group) {
    $show_group = 0;
    if ($group['id'] > 0) {
        foreach ($subscriptions['subscriptions'] as $subscription) {
            if ($subscription['user']['name'] != $query) {
                continue;
            }
            if (!$subscription['groups']) {
                continue;
            }
            // 該使用者已在某群組裡面則不顯示該群組
            foreach ($subscription['groups'] as $hasgroup) {
                if ($group['id'] == $hasgroup['id']) {
                    $show_group = 1;
                    continue;
                }
            }
        }
        if (1 != $show_group) {
?>
              <input type="<?= $type ?>" id="query_<?= $group['id'] ?>" name="query_<?= $group['id'] ?>" value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
<?php
        }
    }
}
?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-2">
            </div>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">加入</button>
            </div>
          </div>
        </form>
        <?php if ($query and $query2) { ?>
        <h3>執行</h3>
        <pre>$pixapi->friend->subscriptions->join_subscription_group('<?= htmlspecialchars($query) ?>', array('group_ids' => '<?= htmlspecialchars($query2) ?>');</pre>
        <h3>執行結果</h3>
        <pre><?php print_r($response); ?></pre>
        <?php }
          }
        ?>
      </div>
    </div>
</div>
</body>
</html>