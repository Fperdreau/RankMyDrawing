% -------------------------------------------------------------------------
% e.g.: [ELO_scores,ranks,subdescription] = import_db(namelist,url,dbname,downloadfold,xlsfolder,recomp)
% -------------------------------------------------------------------------
% Description: ELO ranking system
% -------------------------------------------------------------------------
% Input:
%       namelist: list of participants' name.
%       url: url to the website's root.
%       dbname: Name of the database to export (e.g. refdraw1)
%       downloadfold: download directory (optional. Needed if you wish to
%       move xls files to xlsfolder.
%       xlsfolder: directory where xls files will be moved to (optional)
%       recomp: redo ELO score computation (1: Yes, 2:No (default)
% -------------------------------------------------------------------------
% Output:
%       ELO_score: ELO score
%       ranks: Items' ranking
%       subdescription: Online participants description (number,age,gender)
% -------------------------------------------------------------------------
% Author: Florian Perdreau (florian.perdreau@parisdescartes.fr)
% October 2013
% Update: July 2014
% -------------------------------------------------------------------------
function [ELO_scores,ranks,subdescription] = import_db(namelist,url,dbname,downloadfold,xlsfolder,recomp)
              
    % Parse arguments
    if nargin < 4
        error('Missing arguments!');
    end
    
    if ~exist('xlsfolder','var') || isempty(xlsfolder)
        xlsfolder = downloadfold;
    end
    backupfold = fullfile(xlsfolder,'backup');
    if ~isdir(backupfold)
        mkdir(backupfold);
    end
    
    if ~exist('recomp','var') || isempty(recomp)
        recomp = 0;
    end
    
    % Retrieve database from internet
    url = sprintf('%sadmin/index.php?page=export_sql&REFDRAW=%s',url,dbname);
    table_names = {[dbname,'_comp_mat'],[dbname,'_res_mat'],[dbname,'_ranking'],[dbname,'_list_ip']};
    stat = web(url,'-browser');
    
    if stat == 0
        xlslist = getcontent(downloadfold,'file','xls');
        nfile = numel(xlslist);
        for f = 1:nfile
            cur_file = fullfile(downloadfold,xlslist{f});
            for ta = 1:numel(table_names)
                if ~isempty(strfind(cur_file,table_names{ta})) && ~isempty(strfind(cur_file,dbname))
                    new_name = fullfile(xlsfolder,strcat(table_names{ta},'.xls'));
                    backup = fullfile(backupfold,xlslist{f});
                    copyfile(cur_file,backup);
                    movefile(cur_file,new_name);
                    break
                end
            end
        end
    end
    
    tables = cell(1,numel(table_names));
    for ta = 1:numel(table_names)
        filename = fullfile(xlsfolder,sprintf('%s.xls',table_names{ta}));
        [num,txt,raw] = xlsread(filename);
        
        if ta == 4
            list_ip = raw;
        else
            [r,c] = size(raw);
            cur_table = [];
            for row = 2:r
                for cols = 1:c
                    cur_table(row-1,cols) = str2double(raw{row,cols});
                end
                if ta == 3
                    img_names{row-1} = char(raw(row,2));
                end
            end
        end
        tables{ta} = cur_table;
    end
    subdescription.nsub = size(list_ip,1)-1;
    subdescription.age = [mean(str2double(list_ip(2:end,strcmp(list_ip(1,:),'age')))),stde(str2double(list_ip(2:end,strcmp(list_ip(1,:),'age'))))];
    subdescription.gender = sum(strcmp(list_ip(2:end,strcmp(list_ip(1,:),'gender')),'female'));
    ranking = tables{3};
    
    % Match indices   
    indices = zeros(numel(img_names),1);
    for n = 1:numel(img_names)
        cur_name = (img_names{n});
        exti = strfind(cur_name,'.');
        cur_name = cur_name(1:exti-1);
        ind = strcmp(namelist,cur_name) == 1;
        indices(ind) = n;
    end
    indices = indices(indices>0);
        
    wins = ranking(:,4);
    if recomp == 1
        ELO_scores = redo_compute(list_ip);
    else
        ELO_scores = ranking(:,6);
    end
    ELO_scores = ELO_scores(indices,:);
    [B,r_score] = sort(ELO_scores,'descend');
    
    % Compute ranking
    ranking = ranking(indices,:);    
    wins = ranking(:,2);
    ranks = zeros(numel(wins),1);
    for i = 1:numel(wins)
        ranks(i) = find(r_score == i);
    end
    
    %% Nested functions
    function ELO_scores = redo_compute(list_ip)
        nitems = numel(wins);
        init_score = 1500;
        ELO = ones(nitems,1).*init_score;
        [r,c] = size(list_ip);
        new_compmat = zeros(nitems,nitems);
        new_resmat = zeros(nitems,nitems);
        for rr = 2:r
            subdata = cell(1,4);
            nc = 1;
            for cc = 7:10
                data = list_ip{rr,cc};
                ld = length(data);
                i = 1;
                l = 1;
                ll = 1;
                intdata = [];
                while ll <= ld
                    if ~strcmp(data(ll),',')
                        ll = ll + 1;
                    else
                        intdata(i) = str2double(data(l:ll));
                        i = i + 1;
                        ll = ll + 1;
                        l = ll;
                    end
                end
                subdata{nc} = intdata;
                nc = nc + 1;
            end

            response1 = subdata{1};
            response2 = subdata{2};
            pair1 = subdata{3};
            pair2 = subdata{4};

            % Compute ELO
            ntrial = numel(response1);
            for t = 1:ntrial
                scores = [response1(t),response2(t)];
                items = [pair1(t),pair2(t)];
                new_compmat(pair1(t),pair2(t)) = new_compmat(pair1(t),pair2(t)) + 1;
                new_compmat(pair2(t),pair1(t)) = new_compmat(pair2(t),pair1(t)) + 1;
                new_resmat(pair1(t),pair2(t)) = new_resmat(pair1(t),pair2(t)) + response1(t);
                new_resmat(pair2(t),pair1(t)) = new_resmat(pair2(t),pair1(t)) + response2(t);
                prev_score = [ELO(items(1)),ELO(items(2))];
                nb_match = [sum(new_compmat(pair1(t),:)), sum(new_compmat(pair2(t),:))];
                [ELO_score,pwin] = compute_ELO_score(scores,prev_score,nb_match);
                ELO(items(1)) = ELO_score(1);
                ELO(items(2)) = ELO_score(2);
            end
        end
        
        ELO_scores = ELO;
    end
end