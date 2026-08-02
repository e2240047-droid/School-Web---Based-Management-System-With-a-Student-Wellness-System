<?php

function riskBadge($risk)
{
    switch($risk)
    {
        case "High":
            return '<span class="badge bg-danger">🔴 High Risk</span>';

        case "Medium":
            return '<span class="badge bg-warning text-dark">🟡 Medium Risk</span>';

        default:
            return '<span class="badge bg-success">🟢 Low Risk</span>';
    }
}