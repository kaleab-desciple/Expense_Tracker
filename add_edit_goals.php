<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';
$user_id = $_SESSION['user']['id'];

if(isset($_GET['goal_id'])){
    $update = 1;
    $goal_id = $_GET['goal_id'];
    $goalInfo = get_info("goals", $goal_id);
}else{
    $update = 0;
}

$goals_query = $db->query("SELECT * FROM goals WHERE user_id = $user_id");
$goals = array(); // Initialize an empty array to store the goals

while ($goal = $goals_query->fetch_assoc()) {
    $goals[] = $goal; // Append each row to the $goals array
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
			<strong>Add Goal</strong></h4>
		    
		</div>
		<div class="row justify-content-center">
		    <div class="col-lg-12 mx-auto mbr-form form-col md-pb">
                        <div class="form-wrap" data-form-type="formoid">
                            <form action="process_add_goals.php" method="POST" class="mbr-form form-with-styler col-lg-6 mx-auto" data-form-title="Form Name">
				<input type="hidden" name="update" value="<?=$update?>"/>
				<?php 
				if($update){
				    ?><input type="hidden" name="goal_id" value="<?=$goal_id?>"/><?php
				}
				?>
                                <div class="dragArea form-row">
				    <div class="col-sm-12 form-group">
                                        <input type="text" name="goals[title]" placeholder="GOAL TITLE" value="<?php echo $update ? $goalInfo['title'] : ""; ?>" class="form-control display-7" required="required" />
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label>Target Amount</label>
                                        <input type="number" step="0.01" name="goals[target_amount]" placeholder="Target Amount" value="<?php echo $update ? ($goalInfo['target_amount'] ?? 0) : ""; ?>" class="form-control display-7" />
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label>Current Savings (Allocated)</label>
                                        <input type="number" step="0.01" name="goals[current_amount]" placeholder="Amount Saved" value="<?php echo $update ? ($goalInfo['current_amount'] ?? 0) : ""; ?>" class="form-control display-7" />
                                    </div>
                                    <div class="col-sm-12 form-group">
                                        <label>Deadline</label>
                                        <input type="date" name="goals[deadline]" placeholder="Deadline" value="<?php echo $update ? ($goalInfo['deadline'] ?? "") : ""; ?>" class="form-control display-7" />
                                    </div>
				    <div style="clear: both"></div><br/>
					
				    
                                    <div class="mbr-section-btn">
					<button style="display: inline-block;" type="submit" class="btn btn-sm btn-secondary display-7">Save</button>
					<a style="display: inline-block; margin-left: 20px;" href="goals.php" class="btn btn-sm btn-secondary display-7">Back</a>
				    </div>
				    
                                </div>
                            </form>
                        </div>
                    </div>
		</div>
	    </div>
	</section>

	
	
	<?php include './bottom_scripts.php'; ?>
	<script>
	    $(document).ready(function(){
		
	    });
	</script>


    </body>
</html>