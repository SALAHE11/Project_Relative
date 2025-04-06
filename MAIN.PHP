<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="MAIN.css">
    <link rel="stylesheet" href="normalize.css">
    <link rel="stylesheet" href="css/all.min.css">
    <title>Relative</title>
</head>
<body>
    <?php include 'get_counts.php'; ?>
    <!--start header-->
    <div class="header">
        <div class="container">
            <img class="img" src="logo.png" alt="logo">             
            <div class="links">
                <span class="icon">
                    <span></span> 
                    <span></span>
                    <span></span>
                </span>
                <ul>
                    <li><a href="manage_teachers.php" target="_blank">Teachers</a></li>
                    <li><a href="rooms.php" target="_blank">Rooms</a></li>
                    <li><a href="Branches.php" target="_blank">Branches</a></li>
                    <li><a href="Modules.php" target="_blank">Modules</a></li>
                    <li><a href="module_teacher.php" target="_blank">Modules Teachers</a></li>
                    <li><a href="PERIODES.php" target="_blank">Periods</a></li>
                    <li><a href="Classe_type.php" target="_blank">Classe type</a></li>                    
                    <li><a href="schedule/New.php" target="_blank">Schedules</a></li>
                    <li><a href="schedule/pdf.php" target="_blank">PDF</a></li>
                    <li><a href="Archive.php" target="_blank">Archive</a></li>
                </ul>
             </div>
        </div>
    </div>
    <!--end header-->
    <!--START LANDING SECTION-->
    <div class="landing">
        <div class="intro-text">
            <h1>RELATIVE</h1>
            <p>EFFORTLESSLY CREATE TIMETABLES FOR ALL ACADEMIC BRANCHES
            </p>
        </div>
    </div>
    <!--END LANDING SECTION-->
    <!--START FEATURES-->
    <div class="features">
        <div class="container">
            <div class="feat">
                <i class="fa-regular fa-calendar-days"></i>
                <h3>Schedule Creation</h3>
                <p>Using this webstie will enbale you to creat schedules for different branches</p>
            </div>
            <div class="feat">
                <i class="fa-solid fa-eye"></i>
                <h3>Accessibility</h3>
                <p>All of the data is accessible and customizable as well</p>
            </div>
            <div class="feat">
                <i class="fa-solid fa-circle-info"></i>
                <h3>Notification</h3>
                <p>You will be notified about the numbers of each element</p>
            </div>
        </div>
    </div>
    <!--END FEATURES-->
    <!-- START NUMBERS -->
     <div class="numbers">
        <div class="container">
            <h2 class="special-heading">Numbers</h2>
            <p>Do not be busy, be productive</p>
            <div class="numbers-content">
                <div class="col">
                    <!-- START NUMBER -->
                     <div class="num">
                        <i class="fa-solid fa-user-tie"></i>
                        <div class="text">
                            <h3>Professors</h3>
                            <p><?php echo $professor_count; ?></p>
                        </div>
                     </div> 
                     <div class="num">
                        <i class="fa-solid fa-people-roof"></i>
                        <div class="text">
                            <h3>Classes</h3>
                            <p><?php echo $class_count; ?></p>
                        </div>
                     </div>
                     <!-- END NUMBER -->
                </div>
                <div class="col">
                    <!-- START NUMBER -->
                     <div class="num">
                        <i class="fa-solid fa-code-branch"></i>
                        <div class="text">
                            <h3>Branches</h3>
                            <p><?php echo $branch_count; ?></p>
                        </div>
                     </div> 
                     <div class="num">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <div class="text">
                            <h3>Modules</h3>
                            <p><?php echo $module_count; ?></p>
                        </div>
                     </div>
                     <!-- END NUMBER -->
                </div>
                <div class="col">
                    <div class="image image-x">
                        <img src="administration.jpg" alt="administration">
                    </div>
                </div>
            </div>
        </div>
     </div>
     <!-- END NUMBERS -->
      <!-- START ABOUT -->
       <div class="about">
        <div class="container">
            <h2 class="special-heading">About</h2>
            <p>Less is more work</p>
            <div class="about-content">
                <div class="image">
                    <img src="partner.jpg" alt="partner_picture">
                </div>
                <div class="text">
                    <p>The title <em>Relative</em>  was inspired by Einstein's theory of general relativity, where he concluded that time isn't static for everyone but is, in fact, relative—just as it is for the students in our esteemed school.</p>
                    <hr>
                    <p>This website was created by two junior students in computer engineering Taha Saad and Salaheddine Moujahid</p>
                </div>
            </div>
        </div>
       </div>
       <!-- END ABOUT -->
        <!-- START FOOTER -->
         <div class="footer">
            &copy; 2024 <span>ESTFBS</span> All Rights Reserved
         </div>
         <!-- END FOOTER -->
</body>
</html>