<? if ($req == 'post'): ?>
    <?= isset($error) ? ('<p style="color:red;">' . $error . '</p>') : null ?>
    <?
        if (isset ($smsg))
        {
            print '<p style="color:green;">' . $smsg . '</p>';
            return;
        }
    ?>
<? endif; ?>
<h2>Add User:</h2>
<form action="<?= BASE_URL ?>add" method="post">
    <label>Name: </label><input type="text" name="name" />
    <label>Password: </label><input type="text" name="passwd" />
    <label>Email: </label><input type="text" name="email" />
    <label>Phone: </label><input type="text" name="phone" />
    <label>College: </label><input type="text" name="college" />
    <label>State: </label><input type="text" name="state" />
    <input type="submit" value="Add User" />
</form>