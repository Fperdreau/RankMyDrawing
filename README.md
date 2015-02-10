# RankMyDrawings

RankMyDrawings is a web-based tool designed for helping researchers assessing their participants´ drawing accuracy. It uses an online ELO ranking system (Elo, 1978) that updates drawings´ rank according to their total number of wins against other drawings, the number of times they have been compared as well as their opponents´ current rank. 
In addition to the online experiment, an administration interface allows researcher to manage their databases (adding reference drawings, adding drawings, export databases in XLS format, modify experimental setting or visualize the current ranking).
Finally, a MATLAB function is provided to automatically export the online database in Excel tables and then import it to MATLAB´s workspace.

Current version: 1.2
Requirements: A web server running PHP 5.2 or later and MySQLi (5.0 or later)
