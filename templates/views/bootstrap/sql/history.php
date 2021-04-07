<form action="" method="post" enctype="multipart/form-data" id="form">
    <p>
        <fieldset>
            <legend><a href='#fieldset-history'>History</a>
                <script nonce="Y2FhOWFjYjE4ZDdiOTM1ZjgxNWQzOGQ3MWNmOGNmMGM=">
                    qsl('a').onclick = partial(toggle, 'fieldset-history');
                </script>
            </legend>
            <div id='fieldset-history' class='hidden'>
                <a href="adminer?server=127.0.0.1&amp;username=invoice&amp;sql=&amp;history=2">Edit</a>
                <span class='time' title='2021-04-02'>18:07:54</span>
                <code class='jush-sql'>select * from invoice.gateways;</code><br>
                <a href="adminer?server=127.0.0.1&amp;username=invoice&amp;sql=&amp;history=1">Edit</a>
                <span class='time' title='2021-04-02'>18:07:11</span>
                <code class='jush-sql'>select * from invoice.accounts;</code><br>
                <a href="adminer?server=127.0.0.1&amp;username=invoice&amp;sql=&amp;history=0">Edit</a>
                <span class='time' title='2021-04-02'>18:06:42</span>
                <code class='jush-sql'>select * from invoice.estimates;</code><br>
                <input type='submit' name='clear' value='Clear'>
                <a href='adminer?server=127.0.0.1&amp;username=invoice&amp;sql=&amp;history=all'>Edit all</a>
            </div>
        </fieldset>
    </p>
</form>
