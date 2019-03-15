import {KarmaTest} from "../../../../testing/karma-test";
import {SelectGroupsModalComponent} from "./select-groups-modal.component";
import {GroupService} from "../../admin/groups/group.service";
import {BehaviorSubject} from "rxjs/BehaviorSubject";
import {Group} from "../../shared/models/Group";

describe('SelectGroupsModalComponent', () => {
    let testBed: KarmaTest<SelectGroupsModalComponent>;

    beforeEach(() => {
        testBed = new KarmaTest<SelectGroupsModalComponent>({
            module: {
                declarations: [
                    SelectGroupsModalComponent
                ],
                providers: [GroupService],
            },
            component: SelectGroupsModalComponent
        });
    });

    it('shows modal', () => {
        let groups = testBed.fake('Group', 2);
        spyOn(testBed.get(GroupService), 'getGroups').and.returnValue(new BehaviorSubject({data: groups}));

        testBed.component.show({selected: [1,2,3]});

        //fetches groups from backend
        expect(testBed.get(GroupService).getGroups).toHaveBeenCalledTimes(1);

        //sets specified groups on component
        expect(testBed.component.groups).toEqual(groups);

        //sets disabled groups on component
        expect(testBed.component.disabledGroups).toEqual([1,2,3]);
    });

    it('renders groups', () => {
        testBed.component.groups = testBed.fake('Group', 3);
        testBed.component.disabledGroups = [testBed.component.groups[1].id];
        testBed.component.selectedGroups = [testBed.component.groups[2].id];
        testBed.fixture.detectChanges();

        //renders both groups
        expect(testBed.findAll('.group').length).toEqual(3);

        //disables group
        expect(testBed.findAll('.group')[1].classList.contains('disabled')).toEqual(true);

        //toggles group
        testBed.toggleCheckbox('.group input');
        expect(testBed.component.selectedGroups).toContain(testBed.component.groups[0].id);
        expect(testBed.component.selectedGroups).toContain(testBed.component.groups[2].id);

        //checks checkbox of selected permission
        expect(testBed.findAll('.group input')[2]['checked']).toEqual(true);

        //renders group name
        expect(testBed.find('.group label').textContent.trim()).toEqual(testBed.component.groups[0].name);
    });
});
