<h3 align="center">Battle Outcome</h3>
<h1 align="center" style="color: red"><?php echo $battle['Victor']; ?> is Victorious!!</h1>
<hr><div style="float: right; margin-right: 150px;">END STATE</div><div style="float: left; margin-left: 150px;">INITIAL STATE</div><br><br><hr><br><br>
<?php $csrf->insert('Form_name'); ?>
<div style="float: right;">
    <table>
        <tr>
            <th>Army</th>
            <th>Attack</th>
            <th>Defence</th>
            <th>Mobility</th>
            <th>Morale</th>
            <th>Experience</th>
            <th>Luck</th>
        </tr>
        <?php foreach ($battle['End state'] as $key => $val): ?>
            <tr>
                <th><?php echo $key; ?></th>
                <td><?php echo $val['attack']; ?></td>
                <td><?php echo $val['defence']; ?></td>
                <td><?php echo $val['mobility']; ?></td>
                <td><?php echo $val['morale']; ?></td>
                <td><?php echo $val['experience']; ?></td>
                <td><?php echo $val['luck']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>


<div style="float: left;">
    <table>
        <tr>
            <th>Army</th>
            <th>Attack</th>
            <th>Defence</th>
            <th>Mobility</th>
            <th>Morale</th>
            <th>Experience</th>
            <th>Luck</th>
        </tr>
        <?php foreach ($battle['Initial state'] as $key => $val): ?>
            <tr>
                <th><?php echo $key; ?></th>
                <td><?php echo $val['attack']; ?></td>
                <td><?php echo $val['defence']; ?></td>
                <td><?php echo $val['mobility']; ?></td>
                <td><?php echo $val['morale']; ?></td>
                <td><?php echo $val['experience']; ?></td>
                <td><?php echo $val['luck']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>


<br><br><br><br><br><br><hr>


<div style="float: right;">
    <h3>Legend</h3>
    <b>Attack</b> depends on number of soldiers<br>
    <b>Defence</b> depends on number of soldiers<br>
    <b>Mobility</b> +1 on soldiers death<br>
    <b>Morale</b> +1 on successful defence, -1 on failed attack<br>
    <b>Experience</b> +1 on successful attack<br>
    <b>Luck</b> randomizes with each phase<br>
    <hr>
    <h3>Calculations</h3>
    <b>Attack</b> = (attack + mobility + experience ) * (luck + morale)<br>
    <b>Defence</b> = (defence + mobility + experience ) * (luck + morale)<br>
    <b>Attack wins</b> = attack > defence<br>
    <b>Defence wins</b> = defence >= attack<br>
</div>


<div style="float: left;">
    <h3>History</h3>
<?php foreach ($battle['log'] as $key => $val): ?>
        <b><?php echo $key; ?></b>
	    <?php echo $val; ?><br>
<?php endforeach; ?>
<b>END</b> <?php echo $battle['Victor']; ?> wins!<br>
</div>
