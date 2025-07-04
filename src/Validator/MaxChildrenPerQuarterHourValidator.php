<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Entity\Planning;
use App\Repository\PlanningRepository;

class MaxChildrenPerQuarterHourValidator extends ConstraintValidator
{
    private $planningRepository;

    public function __construct(PlanningRepository $planningRepository)
    {
        $this->planningRepository = $planningRepository;
    }

    public function validate($planning, Constraint $constraint)
    {
        if (!$planning instanceof Planning) {
            return;
        }
        if (!$planning->getDate() || !$planning->getStartTime() || !$planning->getEndTime()) {
            return;
        }
        $date = $planning->getDate()->format('Y-m-d');
        $start = $planning->getStartTime();
        $end = $planning->getEndTime();
        $interval = new \DateInterval('PT15M');
        $period = new \DatePeriod($start, $interval, $end);
        // On ajoute aussi la dernière tranche si end n'est pas pile sur un quart d'heure
        $quarters = [];
        foreach ($period as $dt) {
            $quarters[] = $dt->format('H:i');
        }
        if ($end->format('i') % 15 !== 0 || $end > $start) {
            $quarters[] = $end->format('H:i');
        }
        foreach ($quarters as $quarter) {
            $count = $this->planningRepository->countChildrenForQuarter($date, $quarter, $planning->getId());
            if ($count >= 20) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ time }}', $quarter)
                    ->addViolation();
                return;
            }
        }
    }
} 