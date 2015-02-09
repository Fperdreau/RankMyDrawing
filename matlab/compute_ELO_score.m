% -------------------------------------------------------------------------
% e.g.: [ELO_score,pwin] = compute_ELO_score(scores,prev_score)
% -------------------------------------------------------------------------
% Description: ELO ranking system
% -------------------------------------------------------------------------
% Input:
%       _ scores: match's outcome for each item (1 for win, 0 for loose).
%         This is a 1-by-2 array.
%       _ prev_score: previous ELO score of each item
%       _ nb_match
% -------------------------------------------------------------------------
% Output:
%       _ ELO_score: new ELO score (1-by-2 array)
%       _ pwin: probability of win of each item
% -------------------------------------------------------------------------
% Author: Florian Perdreau
% October 2013
% -------------------------------------------------------------------------

function [ELO_score,pwin] = compute_ELO_score(scores,prev_score,nb_match)

ELO_score = zeros(2,1);
pwin = zeros(2,1);
for i = 1:2
    if i==1
        ind1 = 1;
        ind2 = 2;
    else
        ind1 = 2;
        ind2 = 1;
    end
    match_res = scores(ind1) - scores(ind2);
    diff = prev_score(ind1) - prev_score(ind2);
    pwin(i) = 1/(1+10^(-diff/400));
    
    if match_res == 1
        W = 1;
    elseif match_res == 0
        W = 0.5;
    else
        W = 0;
    end
    
    coef = 800/(2*nb_match(i));

    ELO_score(i) = prev_score(ind1) + coef * (W - pwin(i));
end