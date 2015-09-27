# RankMyDrawings
##Author:
Florian Perdreau - [www.florianperdreau.fr](http://www.florianperdreau.fr)

##Description:
RankMyDrawings is a web-based tool designed for helping researchers assessing their participants´ drawing accuracy. It uses an online ELO ranking system (Elo, 1978) that updates drawings´ rank according to their total number of wins against other drawings, the number of times they have been compared as well as their opponents´ current rank. 
In addition to the online experiment, an administration interface allows researcher to manage their databases (adding reference drawings, adding drawings, export databases in XLS format, modify experimental setting or visualize the current ranking).
Finally, a MATLAB function is provided to automatically export the online database in Excel tables and then import it to MATLAB´s workspace.

##License:
Copyright &copy; 2014 Florian Perdreau

*RankMyDrawings* is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

*RankMyDrawings* is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with RankMyDrawings.  If not, see <http://www.gnu.org/licenses/>.

###External sources
*RankMyDrawings* also depends on several external free softwares:

* PHPMailer, Copyright &copy; 2014 Marcus Bointon, licenced under the [LGPL 2.1 ](http://www.gnu.org/licenses/lgpl-2.1.html "LGPL 2.1").
* html2text, Copyright &copy; 2010 Jevon Wright and others, licenced under the [LGPL 2.1 ](http://www.gnu.org/licenses/lgpl-2.1.html "LGPL 2.1").
* TinyMCE Copyright &copy; Moxiecode Systems AB, licenced under the [LGPL 2.1 ](http://www.gnu.org/licenses/lgpl-2.1.html "LGPL 2.1").

##Requirements:
* A web server running PHP 5.2 or later
* MySQLi (5.0 or later)
* SMTP server or a Google Mail account

##Instructions
Instructions about how to install and use the *RankMyDrawings* can be found in the [manual](manual.md).
