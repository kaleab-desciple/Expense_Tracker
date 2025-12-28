
<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';
$user_id = $_SESSION['user']['id'];


// Perform the query
$sql = "SELECT * FROM goals WHERE user_id = $user_id";
$result = mysqli_query($db, $sql);

// Check for query execution errors
if (!$result) {
    die("Query failed: " . mysqli_error($db));
}

// Fetch the result as an associative array
$goals = array();
while ($row = mysqli_fetch_assoc($result)) {
    $goals[] = $row;
}
?><!DOCTYPE html>
<html  >
    <head>

	<?php include './top_scripts.php'; ?>
    </head>
    <body>

	<?php include './Includes/header.php'; ?>


	<section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">


	    <div class="container">
		<div class="mbr-section-head pb-5">
		    <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
			<strong>MANAGE GOALS</strong></h4>
		    
		</div>
		<div class="row ">
		    
		    <?php
		    if(!empty($goals)){
			?><table class="table table-bordered table-striped table-condensed">
			    <thead>
				<tr>
				    <th>ID</th>
				    <th>Goal</th>
				    <th>Target</th>
				    <th>Saved</th>
				    <th>Progress</th>
				    <th>Deadline</th>
				    <th>Edit</th>
				    <th>Delete</th>
				</tr>
			    </thead>
			    <tbody>
			    <?php
			    foreach($goals as $gol){
                    $target = $gol['target_amount'] ?? 0;
                    $saved = $gol['current_amount'] ?? 0;
                    $percent = ($target > 0) ? round(($saved / $target) * 100) : 0;
                    $percent = min(100, max(0, $percent)); // Clamp between 0 and 100
				?><tr>
				    <td><?=$gol['id']?></td>
				    <td><?=$gol['title']?></td>
				    <td>$<?=number_format($target, 2)?></td>
				    <td>$<?=number_format($saved, 2)?></td>
				    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: <?=$percent?>%;" 
                                 aria-valuenow="<?=$percent?>" aria-valuemin="0" aria-valuemax="100">
                                <?=$percent?>%
                            </div>
                        </div>
                    </td>
				    <td><?=!empty($gol['deadline']) ? date('Y-m-d', strtotime($gol['deadline'])) : 'N/A'?></td>
				    <td><a href="add_edit_goals.php?goal_id=<?=$gol['id']?>" class="btn btn-sm btn-primary btn_extra_small">Edit</a></td>
				    <td><a onclick="return confirm('Are you sure you want to delete this goal? ALL EXPENSES UNDER THIS GOAL WILL BE DELETED')" href="delete_goals.php?goal_id=<?=$gol['id']?>" class="btn btn-sm btn-primary btn_extra_small">Delete</a></td>
				</tr><?php
			    }
			    ?>
			    </tbody>
			</table><?php
		    }else{
			?><h4>No Goals added yet</h4><?php
		    }
		    ?>
		    
		</div>
		<a href="add_edit_goals.php" class="btn btn-primary">Add Goals</a>
	    </div>
	</section>
	<?php include './bottom_scripts.php'; ?>
	<script>
	    $(document).ready(function(){
		
	    });
	</script>


    </body>
</html>
