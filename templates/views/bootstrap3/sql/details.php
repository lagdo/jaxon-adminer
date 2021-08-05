<form action="" method="post">
    <p>
    " . ($num_rows ? ($limit && $num_rows > $limit ?
                                    \adminer\lang("%d / ", $limit) : "") . \adminer\lang("%d row(s)", $num_rows) : "");
                                echo $time;
                                if($connection2 && \preg_match("~^($space|\\()*+SELECT\\b~i", $q) &&
                                    ($explain = \adminer\explain($connection2, $q))) {
                                    echo ", <a href="#$explain_id">Explain</a>" .
                                        \adminer\script("qsl("a").onclick = partial(toggle, "$explain_id");", "");
                                }
                                $id = "export-$commands";
                                echo ", <a href="#$id">" . \adminer\lang("Export") . "</a>" .
                                    \adminer\script("qsl("a").onclick = partial(toggle, "$id");", "") .
    <span id="$id" class="hidden">: "
                                    . \adminer\html_select("output", $adminer->dumpOutput(), $adminer_export["output"]) . " "
                                    . \adminer\html_select("format", $dump_format, $adminer_export["format"])
        <input type="hidden" name="query" value="" \adminer\h($q) >
        <input type="submit" name="export" value="" \adminer\lang("Export") />
        <input type="hidden" name="token" value="$token">
    </span>
</form>
