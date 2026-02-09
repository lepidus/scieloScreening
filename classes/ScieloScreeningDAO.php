<?php

namespace APP\plugins\generic\scieloScreening\classes;

use PKP\db\DAO;
use Illuminate\Support\Facades\DB;

class ScieloScreeningDAO extends DAO
{
    private const SCIELO_JOURNAL_ABBREV = 'SciELO';
    private const SETTINGS_SEARCH_LOCALES = ['pt_BR', 'en'];

    public function getScieloJournalUserGroupId(int $contextId): ?int
    {
        $result = DB::table('user_groups AS ug')
            ->leftJoin('user_group_settings AS ugs', 'ug.user_group_id', '=', 'ugs.user_group_id')
            ->where('ug.context_id', $contextId)
            ->where('ugs.setting_name', 'abbrev')
            ->where('ugs.setting_value', self::SCIELO_JOURNAL_ABBREV)
            ->whereIn('ugs.locale', self::SETTINGS_SEARCH_LOCALES)
            ->first();

        if (is_null($result)) {
            return null;
        }

        return get_object_vars($result)['user_group_id'];
    }

    public function userIsInUserGroup(int $userId, int $userGroupId): bool
    {
        return DB::table('user_user_groups')
            ->where('user_id', $userId)
            ->where('user_group_id', $userGroupId)
            ->exists();
    }
}
