<form method="post">
    <label for="username"><h1>Hello Stranger!</h1><p>I don't know you yet.. How can i call you?</p></label>
    <div>
        <input autofocus type="text" name="username" autocomplete="off" maxlength="15">
        <button name="get_name" value="<?= create_form_id('get_name') ?>" type="submit"><i class="fa-duotone fa-arrow-right-long"></i></button>
    </div>
</form>