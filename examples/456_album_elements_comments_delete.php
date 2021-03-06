<?php
require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/include/checkAuth.php');
$name = $pixapi->getUserName();
$sets = $pixapi->album->sets->search($name)['data'];
if ("" != $_POST['comment_id']) {
    $result = $pixapi->album->elements->comments->delete($_POST['comment_id']);
}
foreach ($sets as $k => $set) {
    $count = $pixapi->album->elements->comments->search($name, ['set_id' => $set['id']])['total'];
    $sets[$k]['title'] .= " ( $count 則留言)";
}
if (!isset($_GET['set_id'])) {
    $current_set = $sets[0];
} else {
    $current_set = $pixapi->album->sets->search($name, ['set_id' => $_GET['set_id']])['data'];
}
$comment_data = $pixapi->album->elements->comments->search($name, ['set_id' => $current_set['id']]);
$comments = $comment_data['total'] ? $comment_data['data'] : 0;
if ($comments) {
    foreach ($comments as $k => $c) {
        if ($comments[$k]['is_spam']) {
            $comments[$k]['body'] .= " (spamed)";
        }
    }
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
    <h1 class="page-header">刪除留言</h1>
    <h3>呼叫方式</h3>
    <pre>$pixapi->album->elements->comments->delete($comment_id);</pre>
    <div class="well">
        <p>必填參數</p>
        <ul>
            <li><p>name</p><p>相簿擁有者</p></li>
            <li><p>comment_id</p><p>該留言 id</p></li>
        </ul>
        <p>選填參數</p>
        <ul>
            <li><p>password</p><p>如果指定使用者的相本被密碼保護，則需要指定這個參數以通過授權</p></li>
        </ul>
    </div>
    <h3><a href="#execute" name="execute">實際測試</a></h3>
    <form action="#execute" class="form-horizontal" role="form" method="POST">
      <div class="form-group">
        <label class="col-sm-2 control-label" for="query">請選擇相簿</label>
        <div class="col-sm-5">
            <select class="form-control" id="query" name="set_id" onchange="updateComment(this.options[this.selectedIndex].value)">
                <?php foreach ($sets as $set) { ?>
                    <?php if ($set['id'] == $current_set['id']) {?>
                <option value="<?= $set['id']?>" selected><?= $set['title']?></option>
                    <?php } else {?>
                <option value="<?= $set['id']?>"><?= $set['title']?></option>
                    <?php } ?>
                <?php } ?>
            </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label" for="query">請選擇留言</label>
        <div class="col-sm-5">
            <select class="form-control" id="query" name="comment_id">
                <?php foreach ($comments as $c) { ?>
                <option value="<?= $c['id']?>"><?= $c['body']?></option>
                <?php } ?>
            </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">刪除此留言</button>
    </form>
    <script>
    var updateComment = function(set_id) {
        var uri = location.pathname;
        var search = location.search;
        var hash = location.hash;
        if (search.indexOf('set_id') > 0) {
            search = search.split('&')[0];
        }
        location = (uri + search + '&set_id=' + set_id + hash);
    }
    </script>
    <?php if (isset($result)) {?>
    <h3>實際執行</h3>
    <pre>
        $pixapi->album->elements->comments->delete('<?= $_POST['comment_id'] ?>')
    </pre>
    <h3>執行結果</h3>
    <pre><?php print_r($result); ?></pre>
    <?php }?>
</div>
</body>
</html>
